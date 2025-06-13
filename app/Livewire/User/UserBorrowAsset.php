<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\BorrowAssetQuantity;
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
        // Get asset IDs that are fully reserved in cart
        $fullyReservedIds = collect($this->selectedAssets)
            ->filter(fn($asset) => $asset['quantity'] >= $asset['original_quantity'])
            ->keys()
            ->toArray();

        $assets = Asset::query()
            ->leftJoin('borrow_asset_quantities', 'borrow_asset_quantities.asset_id', '=', 'assets.id')
            ->join('asset_conditions', 'asset_conditions.id', '=', 'assets.condition_id')
            ->whereIn('asset_conditions.condition_name', ['New', 'Available'])
            ->where(function ($query) use ($fullyReservedIds) {
                $query->where('borrow_asset_quantities.available_quantity', '>', 0)
                    ->orWhereNull('borrow_asset_quantities.available_quantity');
            })
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('assets.name', 'like', '%'.$this->search.'%')
                    ->orWhere('assets.asset_code', 'like', '%'.$this->search.'%')
                    ->orWhere('asset_conditions.condition_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when(!empty($fullyReservedIds), function ($query) use ($fullyReservedIds) {
                $query->whereNotIn('assets.id', $fullyReservedIds);
            })
            ->select('assets.*', 'borrow_asset_quantities.available_quantity')
            ->paginate(10);

        return view('livewire.user.user-borrow-asset', compact('assets'));
    }

    public function addToCart($assetId)
    {
        $asset = Asset::find($assetId);
        $assetQty = BorrowAssetQuantity::where('asset_id', $assetId)->first();

        if (!$assetQty || $assetQty->available_quantity < 1) {
            $this->errorMessage = 'This asset is no longer available for borrowing';
            return;
        }

        if (!isset($this->selectedAssets[$assetId])) {
            $this->selectedAssets[$assetId] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'code' => $asset->asset_code,
                'quantity' => 1,
                'max_quantity' => $assetQty->available_quantity,
                'original_quantity' => $assetQty->available_quantity,
                'purpose' => ''
            ];
        } else {
            // Only allow adding up to available quantity
            if ($this->selectedAssets[$assetId]['quantity'] < $assetQty->available_quantity) {
                $this->selectedAssets[$assetId]['quantity']++;
            } else {
                $this->errorMessage = 'Cannot add more than available quantity for ' . $asset->name;
            }
        }
        
        // Refresh the table to potentially hide this asset
        $this->resetPage();
    }

    public function removeFromCart($assetId)
    {
        if (isset($this->selectedAssets[$assetId])) {
            unset($this->selectedAssets[$assetId]);
            $this->removeFromSelected($assetId);
            
            // Refresh the table to potentially show this asset again
            $this->resetPage();
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
        
        // Refresh the table to show all assets again
        $this->resetPage();
    }

    public function updateCartQuantity($assetId, $newQuantity)
    {
        $newQuantity = (int)$newQuantity;
        if ($newQuantity < 1 || !isset($this->selectedAssets[$assetId])) return;

        $assetQty = BorrowAssetQuantity::where('asset_id', $assetId)->first();
        $available = $assetQty ? $assetQty->available_quantity : 0;

        if ($newQuantity > $available) {
            $this->addError('quantity_'.$assetId, 'Insufficient available quantity');
            $newQuantity = $available;
        }

        $this->selectedAssets[$assetId]['quantity'] = $newQuantity;
        $this->selectedAssets[$assetId]['max_quantity'] = $available;
        
        // Refresh table if quantity changes might affect visibility
        if ($newQuantity === 0 || $newQuantity === $available) {
            $this->resetPage();
        }
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
                    'status' => 'Pending',
                    'remarks' => $this->remarks,
                ]);

                foreach ($this->selectedForBorrow as $assetId) {
                    if (!isset($this->selectedAssets[$assetId])) continue;

                    $item = $this->selectedAssets[$assetId];
                    
                    // Lock the row for update to prevent race conditions
                    $assetQty = BorrowAssetQuantity::where('asset_id', $assetId)->lockForUpdate()->first();

                    if (!$assetQty) {
                        throw new \Exception("Asset '{$item['name']}' is no longer available for borrowing");
                    }

                    // Verify if the quantity has changed since adding to cart
                    $currentAvailable = $assetQty->available_quantity;
                    $originalAvailable = $item['original_quantity'];
                    
                    if ($currentAvailable < $item['quantity']) {
                        // Calculate how much was borrowed by others
                        $borrowedByOthers = $originalAvailable - $currentAvailable;
                        
                        if ($borrowedByOthers > 0) {
                            throw new \Exception(
                                "Available quantity for '{$item['name']}' reduced by $borrowedByOthers due to other users. " .
                                "Current available: $currentAvailable. You requested: {$item['quantity']}."
                            );
                        } else {
                            throw new \Exception(
                                "Insufficient quantity for '{$item['name']}'. " .
                                "Available: $currentAvailable, Requested: {$item['quantity']}"
                            );
                        }
                    }

                    // Create borrow item
                    AssetBorrowItem::create([
                        'borrow_transaction_id' => $transaction->id,
                        'asset_id' => $assetId,
                        'quantity' => $item['quantity'],
                        'purpose' => $item['purpose'] ?? null,
                    ]);

                    // Decrement the available quantity
                    $assetQty->decrement('available_quantity', $item['quantity']);
                }
            });

            // Send email notification
            $this->sendBorrowRequestEmail($transaction);

            $this->showCartModal = false;
            $this->successMessage = 'Borrow request submitted successfully!';
            $this->remarks = '';
            
            foreach ($this->selectedForBorrow as $assetId) {
                unset($this->selectedAssets[$assetId]);
            }
            $this->selectedForBorrow = [];
            
            $this->dispatch('clear-message');
            
            // Refresh the table to reflect new quantities
            $this->resetPage();
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: '.$e->getMessage();
        }
    }

    private function sendBorrowRequestEmail($transaction)
    {
        $user = Auth::user();
        $department = Department::find($user->department_id)->name ?? 'N/A';
        
        // Prepare assets data
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
        
        // Generate email body
        $body = EmailTemplates::borrowRequest(
            $transaction->borrow_code,
            $user->name,
            $user->email,
            $department,
            $this->remarks,
            $requestedAssets
        );
        
        // Get admin emails
        $superAdmin = User::whereHas('role', function($q) {
            $q->where('name', 'Super Admin');
        })->first();
        
        $admins = User::whereHas('role', function($q) {
            $q->where('name', 'Admin');
        })->get();
        
        $to = $superAdmin ? $superAdmin->email : config('mail.from.address');
        $cc = $admins->pluck('email')->toArray();
        
        // Send email
        $emailService = new SendEmail();
        $emailService->send(
            $to,                                          
            "Borrow Request #{$transaction->borrow_code}", 
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