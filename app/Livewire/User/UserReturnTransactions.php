<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\AssetReturnItem;
use App\Models\User;
use App\Models\Department;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; 
use Illuminate\Database\QueryException;


class UserReturnTransactions extends Component
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
            ->whereIn('status', ['Borrowed', 'PendingReturnApproval', 'ReturnRejected', 'PartiallyReturned'])
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['borrowItems.asset', 'user.department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.user.user-return-transactions', [
            'transactions' => $transactions
        ]);
    }

    public function openReturnModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with(['borrowItems' => function ($query) {
            $query->whereIn('status', ['Borrowed', 'ReturnRejected'])
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
            ->whereIn('status', ['Borrowed'])
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
                
                foreach ($selectedBorrowItems as $borrowItem) {
                    AssetReturnItem::create([
                        'return_code' => $returnCode,
                        'borrow_item_id' => $borrowItem->id,
                        'returned_by_user_id' => Auth::id(),
                        'returned_by_department_id' => Auth::user()->department_id,
                        'remarks' => $this->returnRemarks,
                        'returned_at' => now(),
                        'approval_status' => 'Pending', 
                    ]);
                    
                    $borrowItem->update(['status' => 'PendingReturnApproval']);
                }
                
                $updateData = [
                    'return_requested_at' => now(),
                    'return_remarks' => $this->returnRemarks,
                ];
                
                if ($transaction->status === 'ReturnRejected') {
                    $updateData['status'] = 'Borrowed';
                }
                
                $transaction->update($updateData);
                
                $this->sendReturnRequestEmail($transaction->borrow_code, $returnCode, $selectedBorrowItems);
                
                $this->successMessage = "Return request submitted for admin approval!";
                
                $this->reset([
                    'showReturnModal', 
                    'selectedTransaction', 
                    'returnRemarks',
                    'selectedItems',
                    'selectAll'
                ]);
            });
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to process return request: " . $e->getMessage();
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
                    throw new \Exception("Failed to generate unique return code after {$maxAttempts} attempts");
                }
            }
        }
        
        return 'RT-' . now()->format('Ymd-') . Str::uuid();
    }


    private function sendReturnRequestEmail($borrowCode, $returnCode, $selectedBorrowItems)
    {
        try {
            $transaction = AssetBorrowTransaction::with(['user'])
                ->where('borrow_code', $borrowCode)
                ->first();
            
            if (!$transaction || !$transaction->user) {
                throw new \Exception("User information not found for transaction");
            }
            
            $user = $transaction->user;
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
            
            $htmlContent = view('emails.return-request-admin', [
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
                "Return Request: {$returnCode}",
                $htmlContent,  
                $cc,            
                null,         
                null,           
                true           
            );
            
            Log::info("Return request email sent for {$returnCode}");
        } catch (\Exception $e) {
            Log::error("Return request email failed: " . $e->getMessage());
        }
    }
    
    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}