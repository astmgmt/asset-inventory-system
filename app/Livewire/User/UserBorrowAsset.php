<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetCondition;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\SendEmail;
use App\Services\EmailTemplates;

class UserBorrowAsset extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAssets = [];
    public $showCartModal = false;
    public $selectedForBorrow = [];
    public $successMessage = '';
    public $errorMessage = '';
    public $remarks = '';

    public function updatedShowCartModal($value)
    {
        if ($value) {
            $this->selectedForBorrow = array_keys($this->selectedAssets);
            $this->errorMessage = '';
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $assets = Asset::query()
            ->join('asset_conditions', 'asset_conditions.id', '=', 'assets.condition_id')
            ->whereIn('asset_conditions.condition_name', ['New', 'Available'])
            ->where('assets.quantity', '>', 0)
            ->where('assets.is_disposed', false)
            ->whereNotIn('assets.id', array_keys($this->selectedAssets)) // Hide assets in cart
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('assets.name', 'like', '%'.$this->search.'%')
                      ->orWhere('assets.asset_code', 'like', '%'.$this->search.'%')
                      ->orWhere('asset_conditions.condition_name', 'like', '%'.$this->search.'%');
                });
            })
            ->select('assets.*', 'asset_conditions.condition_name')
            ->paginate(10);

        return view('livewire.user.user-borrow-asset', compact('assets'));
    }

    public function addToCart($assetId)
    {
        $this->errorMessage = '';
        $asset = Asset::find($assetId);

        if ($asset->quantity < 1) {
            $this->errorMessage = 'This asset is no longer available for borrowing';
            return;
        }

        if (!isset($this->selectedAssets[$assetId])) {
            $this->selectedAssets[$assetId] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'code' => $asset->asset_code,
                'quantity' => 1,
                'max_quantity' => $asset->quantity,
                'purpose' => ''
            ];
        } else {
            if ($this->selectedAssets[$assetId]['quantity'] < $asset->quantity) {
                $this->selectedAssets[$assetId]['quantity']++;
                $this->selectedAssets[$assetId]['max_quantity'] = $asset->quantity;
            } else {
                $this->errorMessage = 'Cannot add more than available quantity for ' . $asset->name;
            }
        }
    }

    public function removeFromCart($assetId)
    {
        if (isset($this->selectedAssets[$assetId])) {
            unset($this->selectedAssets[$assetId]);
            $this->removeFromSelected($assetId);
        }
    }

    public function removeFromSelected($assetId)
    {
        $key = array_search($assetId, $this->selectedForBorrow);
        if ($key !== false) {
            unset($this->selectedForBorrow[$key]);
        }
    }

    public function clearCart()
    {
        $this->selectedAssets = [];
        $this->selectedForBorrow = [];
        $this->remarks = '';
    }

    public function updateCartQuantity($assetId, $newQuantity)
    {
        $newQuantity = (int)$newQuantity;
        if ($newQuantity < 1 || !isset($this->selectedAssets[$assetId])) return;

        $asset = Asset::find($assetId);
        $available = $asset->quantity;

        if ($newQuantity > $available) {
            $this->addError('quantity_'.$assetId, 'Insufficient available quantity');
            $newQuantity = $available;
        }

        $this->selectedAssets[$assetId]['quantity'] = $newQuantity;
        $this->selectedAssets[$assetId]['max_quantity'] = $available;
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function toggleSelectAll()
    {
        if (count($this->selectedForBorrow) === count($this->selectedAssets)) {
            $this->selectedForBorrow = [];
        } else {
            $this->selectedForBorrow = array_keys($this->selectedAssets);
        }
    }

    public function borrow()
    {
        $this->errorMessage = '';
        
        if (empty($this->selectedForBorrow)) {
            $this->errorMessage = 'Select asset first before proceeding!';
            return;
        }

        try {
            $transaction = null;
            DB::transaction(function () use (&$transaction) {
                $transaction = AssetBorrowTransaction::create([
                    'borrow_code' => $this->generateBorrowCode(),
                    'user_id' => Auth::id(),
                    'user_department_id' => Auth::user()->department_id,
                    'requested_by_user_id' => Auth::id(),
                    'requested_by_department_id' => Auth::user()->department_id,
                    'status' => 'PendingBorrowApproval',
                    'remarks' => $this->remarks,
                ]);

                foreach ($this->selectedForBorrow as $assetId) {
                    if (!isset($this->selectedAssets[$assetId])) continue;

                    $item = $this->selectedAssets[$assetId];
                    $asset = Asset::with('condition')
                        ->where('id', $assetId)
                        ->lockForUpdate()
                        ->first();

                    if (!$asset) {
                        throw new \Exception("Asset '{$item['name']}' not found");
                    }

                    // Check conditions
                    if ($asset->is_disposed) {
                        throw new \Exception("Asset '{$item['name']}' has been disposed");
                    }

                    $allowedConditions = ['New', 'Available'];
                    if (!in_array($asset->condition->condition_name, $allowedConditions)) {
                        throw new \Exception("Asset '{$item['name']}' is not available for borrowing");
                    }

                    // Use available quantity (quantity - reserved_quantity)
                    $available = $asset->quantity - $asset->reserved_quantity;
                    
                    if ($available < $item['quantity']) {
                        throw new \Exception(
                            "Insufficient available quantity for '{$item['name']}'. " .
                            "Available: {$available}, Requested: {$item['quantity']}"
                        );
                    }

                    // Reserve the quantity
                    $asset->increment('reserved_quantity', $item['quantity']);

                    // Create borrow item
                    AssetBorrowItem::create([
                        'borrow_transaction_id' => $transaction->id,
                        'asset_id' => $assetId,
                        'quantity' => $item['quantity'],
                        'purpose' => $item['purpose'] ?? null,
                    ]);
                }
            });

            // Send email
            $this->sendBorrowRequestEmail($transaction);

            // Clear cart and show success
            $this->clearCart();
            $this->successMessage = 'Borrow request submitted successfully!';
            $this->showCartModal = false;

        } catch (\Exception $e) {
            $this->errorMessage = 'Error: '.$e->getMessage();
        }
    }



    private function sendBorrowRequestEmail($transaction)
    {
        $user = Auth::user();
        $department = Department::find($user->department_id)->name ?? 'N/A';
        
        $requestedAssets = [];
        foreach ($this->selectedForBorrow as $assetId) {
            if (!isset($this->selectedAssets[$assetId])) continue;
            
            $asset = $this->selectedAssets[$assetId];
            $requestedAssets[] = [
                'name' => $asset['name'],
                'code' => $asset['code'],
                'quantity' => $asset['quantity'],
                'purpose' => $asset['purpose'] ?? null,
            ];
        }
        
        $body = EmailTemplates::borrowRequest(
            $transaction->borrow_code,
            $user->name,
            $user->email,
            $department,
            $this->remarks,
            $requestedAssets
        );
        
        $superAdmin = User::whereHas('role', function($q) {
            $q->where('name', 'Super Admin');
        })->first();
        
        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'Admin');
        })->get();
        
        $to = $superAdmin ? $superAdmin->email : config('mail.from.address');
        $cc = $admins->pluck('email')->toArray();
        
        $emailService = new SendEmail();
        $emailService->send(
            $to,                                          
            "Borrow Request #{$transaction->borrow_code} (Pending Approval)", 
            $body,                                        
            $cc,                                         
            null,                                         
            null,                                         
            true                                         
        );
    }

    private function generateBorrowCode()
    {
        $date = now()->format('Ymd');
        $lastCode = AssetBorrowTransaction::where('borrow_code', 'like', "BR-{$date}-%")
            ->orderBy('borrow_code', 'desc')
            ->first();

        $lastNum = $lastCode ? intval(substr($lastCode->borrow_code, -8)) : 0;
        $newNum = str_pad($lastNum + 1, 8, '0', STR_PAD_LEFT);

        return "BR-{$date}-{$newNum}";
    }
}