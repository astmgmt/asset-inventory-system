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
    public $isProcessing = false; 

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
            ->join('asset_categories', 'asset_categories.id', '=', 'assets.category_id')
            ->whereIn('asset_conditions.condition_name', ['New', 'Available'])
            ->where('assets.is_disposed', false)
            ->whereNotIn('assets.id', array_keys($this->selectedAssets))
            ->whereRaw('(assets.quantity - assets.reserved_quantity) > 0')
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('assets.name', 'like', '%'.$this->search.'%')
                    ->orWhere('assets.asset_code', 'like', '%'.$this->search.'%')
                    ->orWhere('asset_conditions.condition_name', 'like', '%'.$this->search.'%');
                });
            })
            ->select(
                'assets.*', 
                'asset_conditions.condition_name',
                'asset_categories.category_name',
                DB::raw('(assets.quantity - assets.reserved_quantity) as available_quantity')
            )
            ->paginate(10);

        return view('livewire.user.user-borrow-asset', compact('assets'));
    }

    public function addToCart($assetId)
    {
        $this->errorMessage = '';
        
        $asset = Asset::select('assets.*', 
                    DB::raw('(quantity - reserved_quantity) as available_quantity')
                )->find($assetId);

        if ($asset->available_quantity < 1) {
            $this->errorMessage = 'This asset is no longer available for borrowing';
            return;
        }

        if (!isset($this->selectedAssets[$assetId])) {
            $this->selectedAssets[$assetId] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'model_number' => $asset->model_number,
                'code' => $asset->asset_code,
                'quantity' => 1,
                'max_quantity' => $asset->available_quantity,
                'purpose' => ''
            ];
        } else {
            $currentAvailable = $asset->available_quantity;
            
            if ($this->selectedAssets[$assetId]['quantity'] < $currentAvailable) {
                $this->selectedAssets[$assetId]['quantity']++;
                $this->selectedAssets[$assetId]['max_quantity'] = $currentAvailable;
            } else {
                $this->errorMessage = 'Cannot add more than available quantity for ' . $asset->name;
            }
        }
    }

    public function updateCartQuantity($assetId, $newQuantity)
    {
        $newQuantity = (int)$newQuantity;
        if ($newQuantity < 1 || !isset($this->selectedAssets[$assetId])) return;

        $asset = Asset::select('assets.*', 
                    DB::raw('(quantity - reserved_quantity) as available_quantity')
                )->find($assetId);
                
        $available = $asset->available_quantity;

        if ($newQuantity > $available) {
            $this->addError('quantity_'.$assetId, 'Insufficient available quantity');
            $newQuantity = $available;
        }

        $this->selectedAssets[$assetId]['quantity'] = $newQuantity;
        $this->selectedAssets[$assetId]['max_quantity'] = $available;
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

                    if ($asset->is_disposed) {
                        throw new \Exception("Asset '{$item['name']}' has been disposed");
                    }

                    $allowedConditions = ['New', 'Available'];
                    if (!in_array($asset->condition->condition_name, $allowedConditions)) {
                        throw new \Exception("Asset '{$item['name']}' is not available for borrowing");
                    }

                    $available = $asset->quantity - $asset->reserved_quantity;
                    
                    if ($available < $item['quantity']) {
                        throw new \Exception(
                            "Insufficient available quantity for '{$item['name']}'. " .
                            "Available: {$available}, Requested: {$item['quantity']}"
                        );
                    }

                    $asset->increment('reserved_quantity', $item['quantity']);

                    AssetBorrowItem::create([
                        'borrow_transaction_id' => $transaction->id,
                        'asset_id' => $assetId,
                        'quantity' => $item['quantity'],
                        'purpose' => $item['purpose'] ?? null,
                    ]);
                }
            });

            $this->sendBorrowRequestEmail($transaction);
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
        
        $superAdmins = User::whereHas('role', function($q) {
                $q->where('name', 'Super Admin');
            })
            ->orderBy('id')
            ->get();

        $admins = User::whereHas('role', function($q) {
                $q->where('name', 'Admin');
            })
            ->get();

        $to = $superAdmins->isNotEmpty() 
            ? $superAdmins->first()->email 
            : config('mail.from.address');

        $cc = collect();
        
        if ($superAdmins->count() > 1) {
            $cc = $cc->merge(
                $superAdmins->slice(1)->pluck('email')
            );
        }
        
        $cc = $cc->merge(
            $admins->pluck('email')
        );
        
        $cc = $cc->unique()->values()->toArray();

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