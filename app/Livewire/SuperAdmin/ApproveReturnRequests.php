<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\Asset;
use App\Models\UserHistory;
use App\Models\AssetReturnItem;
use App\Services\SendEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveReturnRequests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showApproveModal = false;
    public $showRejectModal = false;
    public $approveRemarks = '';
    public $rejectRemarks = '';
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        $transactions = AssetBorrowTransaction::whereHas('borrowItems.returnItems', function ($query) {
            $query->where('approval_status', 'Pending');
        })
            ->when($this->search, function ($query) {
                $query->where('borrow_code', 'like', '%'.$this->search.'%');
            })
            ->with(['user', 'borrowItems.asset', 'borrowItems.returnItems'])
            ->orderBy('return_requested_at', 'desc')
            ->paginate(10);


        return view('livewire.super-admin.approve-return-requests', [
            'transactions' => $transactions
        ]);
    }

    public function openApproveModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with(['borrowItems.asset', 'borrowItems.returnItems'])
            ->findOrFail($transactionId);

        // Filter borrowItems to only those that have pending returnItems
        $this->selectedTransaction->borrowItems = $this->selectedTransaction->borrowItems->filter(function ($borrowItem) {
            return $borrowItem->returnItems->contains('approval_status', 'Pending');
        });

        $this->showApproveModal = true;
    }


    public function openRejectModal($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
        $this->showRejectModal = true;
    }

    public function approveReturn()
    {
        $this->validate([
            'approveRemarks' => 'nullable|string|max:500',
        ]);
        
        $pendingReturnItems = null;
        try {
            DB::transaction(function () use (&$pendingReturnItems) {
                $transaction = $this->selectedTransaction;
                
                // Get all pending return items for this transaction
                $pendingReturnItems = AssetReturnItem::with('borrowItem.asset') // eager load borrowItem and asset
                    ->whereHas('borrowItem', function ($query) use ($transaction) {
                        $query->where('borrow_transaction_id', $transaction->id);
                    })
                    ->where('approval_status', 'Pending')
                    ->get();


                if ($pendingReturnItems->isEmpty()) {
                    throw new \Exception('No pending return requests found for approval.');
                }

                // 1. Update asset_return_items to "Approved"
                foreach ($pendingReturnItems as $returnItem) {
                    $returnItem->update([
                        'approval_status' => 'Approved',
                        'approved_at' => now(),
                        'approved_by_user_id' => Auth::id(),
                    ]);
                }

                // 2. Update asset_borrow_items to "Returned"
                foreach ($pendingReturnItems as $returnItem) {
                    $borrowItem = $returnItem->borrowItem;
                    $borrowItem->update(['status' => 'Returned']);
                    
                    // Increment asset quantity
                    $asset = $borrowItem->asset;
                    $asset->increment('quantity', $borrowItem->quantity);
                }

                // 3. Update asset_borrow_transactions status
                // Check if all items in the transaction are returned
                $allItemsReturned = $transaction->borrowItems->every(function ($item) {
                    return $item->status === 'Returned';
                });
                
                $transaction->update([
                    'status' => $allItemsReturned ? 'Returned' : 'Borrowed',
                    'returned_at' => $allItemsReturned ? now() : null,
                    'approved_by_user_id' => Auth::id(),
                    'approved_at' => now(),
                    'remarks' => $this->approveRemarks,
                ]);
            });

            // Send email notification
            $returnCode = $pendingReturnItems->first()->return_code ?? 'RT-' . now()->format('Ymd');
            $this->sendReturnApprovalEmail($this->selectedTransaction, $returnCode);

            $this->successMessage = "Return approved successfully!";
            $this->reset(['showApproveModal', 'selectedTransaction', 'approveRemarks']);
        } catch (\Exception $e) {
            $this->errorMessage = "Error approving return: " . $e->getMessage();
            Log::error("Approve return error: " . $e->getMessage());
        }
    }



    
    private function generateReturnCode()
    {
        $date = now()->format('Ymd');
        $lastReturn = UserHistory::where('return_code', 'like', "RT-{$date}-%")
            ->orderBy('return_code', 'desc')
            ->first();
            
        $number = $lastReturn ? (int)substr($lastReturn->return_code, -8) + 1 : 1;
        
        return sprintf("RT-%s-%08d", $date, $number);
    }
    
    private function generateReturnApprovalPDF($transaction, $returnCode)
    {
        $pdf = Pdf::loadView('pdf.return-approval', [
            'transaction' => $transaction,
            'returnCode' => $returnCode,
            'approvalDate' => now()->format('M d, Y H:i'),
            'approver' => Auth::user(),
        ]);
        
        return $pdf->output();
    }
    
    private function sendReturnApprovalEmail($transaction, $returnCode)
    {
        try {
            $emailService = new SendEmail();
            $user = $transaction->user;
            $approverName = Auth::user()->name;
            
            // Prepare email content in required format
            $emailContent = [
                'emails.return-approved-user',
                [
                    'returnCode' => $returnCode,
                    'approverName' => $approverName,
                    'approvalDate' => now()->format('M d, Y H:i')
                ]
            ];
            
            // Send email without PDF attachment
            $emailService->send(
                $user->email,
                "Return Approved: {$returnCode}",
                $emailContent,
                [],
                null,  // No PDF content
                null,  // No attachment name
                false
            );
        } catch (\Exception $e) {
            Log::error("Return approval email failed: " . $e->getMessage());
        }
    }

    public function rejectReturn()
    {
        $this->validate([
            'rejectRemarks' => 'required|string|max:500',
        ]);

        try {
            $this->selectedTransaction->update([
                'status' => 'ReturnRejected',
                'remarks' => $this->rejectRemarks
            ]);
            
            $this->successMessage = "Return rejected successfully!";
            $this->reset(['showRejectModal', 'selectedTransaction', 'rejectRemarks']);
        } catch (\Exception $e) {
            $this->errorMessage = "Error rejecting return: " . $e->getMessage();
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}