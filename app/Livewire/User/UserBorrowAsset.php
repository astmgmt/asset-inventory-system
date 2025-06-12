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
use App\Models\Role;
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
            ->leftJoin('borrow_asset_quantities', 'borrow_asset_quantities.asset_id', '=', 'assets.id')
            ->join('asset_conditions', 'asset_conditions.id', '=', 'assets.condition_id')
            ->whereIn('asset_conditions.condition_name', ['New', 'Available'])
            ->where(function ($query) {
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
            ->select('assets.*', 'borrow_asset_quantities.available_quantity')
            ->paginate(10);

        return view('livewire.user.user-borrow-asset', compact('assets'));
    }

    public function addToCart($assetId)
    {
        $asset = Asset::join('borrow_asset_quantities', 'borrow_asset_quantities.asset_id', '=', 'assets.id')
            ->where('assets.id', $assetId)
            ->select('assets.*', 'borrow_asset_quantities.available_quantity')
            ->first();
        
        if (!$asset || $asset->available_quantity < 1) {
            $this->errorMessage = 'This asset is no longer available for borrowing';
            return;
        }

        if (!isset($this->selectedAssets[$assetId])) {
            $this->selectedAssets[$assetId] = [
                'id' => $asset->id,
                'name' => $asset->name,
                'code' => $asset->asset_code,
                'quantity' => 1,
                'max_quantity' => $asset->available_quantity,
                'purpose' => ''
            ];
        } elseif ($this->selectedAssets[$assetId]['quantity'] < $this->selectedAssets[$assetId]['max_quantity']) {
            $this->selectedAssets[$assetId]['quantity']++;
        }
    }

    public function removeFromCart($assetId)
    {
        unset($this->selectedAssets[$assetId]);
        $this->removeFromSelected($assetId);
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
                    $assetQty = BorrowAssetQuantity::where('asset_id', $assetId)->first();

                    if (!$assetQty || $assetQty->available_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient quantity for {$item['name']}");
                    }

                    AssetBorrowItem::create([
                        'borrow_transaction_id' => $transaction->id,
                        'asset_id' => $assetId,
                        'quantity' => $item['quantity'],
                        'purpose' => $item['purpose'] ?? null,
                    ]);

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
            config('mail.from.address'), // From address
            "Borrow Request #{$transaction->borrow_code}", // Subject
            $body, // HTML content
            $to, // To (Super Admin)
            $cc // CC (Admins)
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