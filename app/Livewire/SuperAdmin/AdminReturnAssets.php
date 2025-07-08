<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetReturnItem;
use App\Models\User;
use App\Models\UserHistory; 
use App\Models\Department;
use App\Models\AssetCondition;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

class AdminReturnAssets extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showReturnModal = false;
    public $showViewModal = false;
    public $showConfirmationModal = false;
    public $returnRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';
    public $selectedItems = [];
    public $selectAll = true;

    public function render()
    {
        $transactions = AssetBorrowTransaction::where('user_id', Auth::id())
            ->whereIn('status', ['Borrowed', 'PartiallyReturned'])
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['borrowItems.asset', 'user.department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.admin-return-assets', [
            'transactions' => $transactions
        ]);
    }

    public function openReturnModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with(['borrowItems' => function ($query) {
            $query->where('status', 'Borrowed')
                ->with('asset');
        }])->findOrFail($transactionId);

        $this->returnRemarks = '';
        $this->selectedItems = $this->selectedTransaction->borrowItems->pluck('id')->toArray();
        $this->selectAll = true;
        $this->showReturnModal = true;
    }

    public function openViewModal($transactionId)
    {
        $transaction = AssetBorrowTransaction::findOrFail($transactionId);

        $filteredBorrowItems = AssetBorrowItem::where('borrow_transaction_id', $transaction->id)
            ->where('status', 'Borrowed')
            ->with('asset')
            ->get();

        $transaction->setRelation('borrowItems', $filteredBorrowItems);

        $this->selectedTransaction = $transaction;
        $this->showViewModal = true;
    }

    public function updatedSelectAll($value)
    {
        $this->selectedItems = $value
            ? $this->selectedTransaction->borrowItems->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedItems()
    {
        $this->selectAll = count($this->selectedItems) === 
                          $this->selectedTransaction->borrowItems->count();
    }

    public function confirmReturn()
    {
        $this->validate([
            'returnRemarks' => 'nullable|string|max:500',
        ]);
        
        if (empty($this->selectedItems)) {
            $this->errorMessage = "Please select at least one asset to return.";
            return;
        }
        
        $this->showConfirmationModal = true;
    }
    
    public function processReturn()
    {
        $this->showConfirmationModal = false;
        try {
            DB::transaction(function () {
                $transaction = $this->selectedTransaction;
                
                $returnCode = $this->generateUniqueReturnCode();
                
                $selectedBorrowItems = AssetBorrowItem::whereIn('id', $this->selectedItems)
                    ->where('borrow_transaction_id', $transaction->id)
                    ->where('status', 'Borrowed')
                    ->with('asset')
                    ->get();
                
                if ($selectedBorrowItems->isEmpty()) {
                    throw new \Exception("No valid assets found for return");
                }

                // Get Available condition
                $availableCondition = AssetCondition::where('condition_name', 'Available')->first();
                
                // Prepare return data for history
                $returnItemsData = [];
                
                foreach ($selectedBorrowItems as $borrowItem) {
                    // Create return record
                    AssetReturnItem::create([
                        'return_code' => $returnCode,
                        'borrow_item_id' => $borrowItem->id,
                        'returned_by_user_id' => Auth::id(),
                        'returned_by_department_id' => Auth::user()->department_id,
                        'remarks' => $this->returnRemarks,
                        'returned_at' => now(),
                        'approval_status' => 'Approved',
                    ]);
                    
                    // Update borrow item status
                    $borrowItem->update(['status' => 'Returned']);
                    
                    // Update asset reserved_quantity and condition
                    if ($borrowItem->asset) {
                        $asset = $borrowItem->asset;
                        
                        // Decrement reserved quantity
                        $asset->decrement('reserved_quantity', $borrowItem->quantity);
                        
                        // Update condition if asset is fully returned
                        if ($asset->reserved_quantity == 0 && $availableCondition) {
                            $asset->update(['condition_id' => $availableCondition->id]);
                        }
                    }
                    
                    // Collect data for user history
                    $returnItemsData[] = [
                        'asset_code' => $borrowItem->asset->asset_code,
                        'asset_name' => $borrowItem->asset->name,
                        'model' => $borrowItem->asset->model_number,
                        'serial' => $borrowItem->asset->serial_number,
                        'quantity' => $borrowItem->quantity,
                        'returned_at' => now()->format('Y-m-d H:i:s')
                    ];
                }
                
                // Update transaction status
                $remainingItems = AssetBorrowItem::where('borrow_transaction_id', $transaction->id)
                    ->where('status', 'Borrowed')
                    ->count();
                    
                if ($remainingItems > 0) {
                    $transaction->update(['status' => 'PartiallyReturned']);
                    $historyStatus = 'PartiallyReturned';
                } else {
                    $transaction->update(['status' => 'Returned']);
                    $historyStatus = 'Returned';
                }
                
                // Create user history record
                UserHistory::create([
                    'user_id' => Auth::id(),
                    'borrow_code' => $transaction->borrow_code,
                    'return_code' => $returnCode,
                    'status' => 'Return Approved', // Matches enum values
                    'return_data' => [
                        'items' => $returnItemsData,
                        'remarks' => $this->returnRemarks,
                        'returned_by' => Auth::user()->name,
                        'department' => Auth::user()->department->name ?? 'N/A',
                        'returned_at' => now()->format('Y-m-d H:i:s'),
                        'transaction_status' => $remainingItems > 0 ? 'PartiallyReturned' : 'Returned'
                    ],
                    'action_date' => now()
                ]);
                
                // Send email receipt
                $this->sendReturnReceipt($transaction->borrow_code, $returnCode, $selectedBorrowItems);
                
                $this->successMessage = "Assets returned successfully!";
                
                $this->reset([
                    'showReturnModal', 
                    'selectedTransaction', 
                    'returnRemarks',
                    'selectedItems',
                    'selectAll'
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Admin return failed: " . $e->getMessage());
            $this->errorMessage = "Failed to process return: " . $e->getMessage();
        }
    }

    private function generateUniqueReturnCode()
    {
        $datePart = now()->format('Ymd');
        $maxAttempts = 5;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $lastId = AssetReturnItem::where('return_code', 'like', "RT-{$datePart}-%")
                    ->orderBy('id', 'desc')
                    ->value('return_code');
                
                $counter = $lastId ? intval(substr($lastId, -8)) + 1 : 1;
                $paddedCounter = str_pad($counter, 8, '0', STR_PAD_LEFT);
                $code = "RT-{$datePart}-{$paddedCounter}";
                
                if (!AssetReturnItem::where('return_code', $code)->exists()) {
                    return $code;
                }
            } catch (\Exception $e) {
                if ($attempt === $maxAttempts) {
                    return 'RT-' . now()->format('Ymd-') . Str::uuid();
                }
            }
        }
    }

    private function sendReturnReceipt($borrowCode, $returnCode, $selectedBorrowItems)
    {
        try {
            $user = Auth::user();
            $department = $user->department->name ?? 'N/A';
            
            $assetsList = [];
            foreach ($selectedBorrowItems as $item) {
                $assetsList[] = [
                    'code' => $item->asset->asset_code,
                    'name' => $item->asset->name,
                    'model' => $item->asset->model_number,
                    'serial' => $item->asset->serial_number,
                    'quantity' => $item->quantity
                ];
            }
            
            // Get all admins and super admins
            $admins = User::whereHas('role', function($q) {
                    $q->whereIn('name', ['Admin', 'Super Admin']);
                })
                ->get();
            
            $to = $admins->isNotEmpty() 
                ? $admins->first()->email 
                : config('mail.from.address');
            
            $cc = $admins->slice(1)->pluck('email')->toArray();
            
            $htmlContent = view('emails.admin-return-assets', [
                'returnCode' => $returnCode,
                'borrowCode' => $borrowCode,
                'userName' => $user->name,
                'department' => $department,
                'returnDate' => now()->format('M d, Y H:i'),
                'remarks' => $this->returnRemarks,
                'assetsList' => $assetsList
            ])->render();
            
            $emailService = new SendEmail();
            $emailService->send(
                $to,
                "Return Receipt: {$returnCode}",
                $htmlContent,
                $cc
            );
            
            Log::info("Return receipt sent for {$returnCode}");
        } catch (\Exception $e) {
            Log::error("Return receipt email failed: " . $e->getMessage());
        }
    }
    
    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}