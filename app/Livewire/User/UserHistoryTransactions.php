<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\UserHistory;
use App\Models\AssetBorrowTransaction;
use App\Models\User;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

#[Layout('components.layouts.app')]
class UserHistoryTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHistory = null;
    public $showDetailsModal = false;
    public $showDeleteModal = false;
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        $history = UserHistory::where('user_id', Auth::id())
            ->whereNotIn('status', ['Borrow Approved']) // Exclude regular borrow approvals
            ->where(function ($query) {
                $query->where('status', 'Borrow Denied')
                    ->orWhere('status', 'Return Approved')
                    ->orWhere('status', 'Return Denied')
                    // Show borrow approvals that have not been hidden
                    ->orWhere(function ($q) {
                        $q->where('status', 'Borrow Approved')
                            ->whereNull('return_code');
                    });
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('borrow_code', 'like', '%'.$this->search.'%')
                    ->orWhere('return_code', 'like', '%'.$this->search.'%')
                    ->orWhere('status', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('action_date', 'desc')
            ->paginate(10);

        return view('livewire.user.user-history-transactions', [
            'history' => $history
        ]);
    }



    public function showDetails($historyId)
    {
        $history = UserHistory::findOrFail($historyId);

        if (is_null($history->borrow_data)) {
            $reference = UserHistory::where('borrow_code', $history->borrow_code)
                ->whereNotNull('borrow_data')
                ->first();

            if ($reference) {
                $history->borrow_data = $reference->borrow_data;
                $history->save();
            }
        }

        $this->selectedHistory = UserHistory::findOrFail($historyId)->fresh();
        $this->showDetailsModal = true;
    }

    public function confirmDelete($historyId)
    {
        $this->selectedHistory = UserHistory::findOrFail($historyId);
        $this->showDeleteModal = true;
    }

    public function deleteHistory()
    {
        try {
            $history = $this->selectedHistory;
            $this->sendDeletionEmail($history);
            $history->delete();

            $this->successMessage = "History record deleted successfully!";
            $this->reset(['showDeleteModal', 'selectedHistory']);
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to delete history: " . $e->getMessage();
        }
    }
    
    public function generatePdf($historyId)
    {
        $history = UserHistory::findOrFail($historyId);
        $user = Auth::user();
        
        $pdf = Pdf::loadView('pdf.borrow-history', [
            'history' => $history,
            'user' => $user
        ]);
        
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "history-{$history->borrow_code}.pdf"
        );
    }
    
    public function generateHistoryPdf($historyId)
    {
        $history = UserHistory::findOrFail($historyId);
        $user = Auth::user();

        if ($history->status === 'Borrow Denied') {
            $this->errorMessage = "Cannot generate PDF for denied borrow requests";
            return;
        }
        
        // Get the borrow approval record
        $borrowApprovalRecord = UserHistory::where('borrow_code', $history->borrow_code)
            ->where('status', 'Borrow Approved')
            ->first();
        
        // Get the borrow approver name
        $borrowApprovedBy = 'N/A';
        if ($borrowApprovalRecord) {
            // Check if borrow_data has approved_by
            if (isset($borrowApprovalRecord->borrow_data['approved_by'])) {
                $borrowApprovedBy = $borrowApprovalRecord->borrow_data['approved_by'];
            }
            // Check if borrow_data has approved_by_user_id
            elseif (isset($borrowApprovalRecord->borrow_data['approved_by_user_id'])) {
                $approver = User::find($borrowApprovalRecord->borrow_data['approved_by_user_id']);
                $borrowApprovedBy = $approver ? $approver->name : 'N/A';
            }
            // Check if transaction has approved_by_user_id
            elseif (isset($borrowApprovalRecord->borrow_data['id'])) {
                $transaction = AssetBorrowTransaction::find($borrowApprovalRecord->borrow_data['id']);
                if ($transaction && $transaction->approvedByUser) {
                    $borrowApprovedBy = $transaction->approvedByUser->name;
                }
            }
        }
        
        // Get the return received by name
        $returnReceivedBy = $history->return_data['return_received_by'] ?? 'N/A';
        
        // Extract transaction details
        $transactionData = [
            'borrow_code' => $history->borrow_code,
            'return_code' => $history->return_code,
            'user' => [
                'name' => $user->name,
                'department' => $user->department->name ?? 'N/A',
            ],
            'borrow_approved_by' => $borrowApprovedBy,
            'return_received_by' => $returnReceivedBy,
            'borrowed_at' => $history->action_date->format('M d, Y H:i'),
            'returned_at' => $history->return_date ? $history->return_date->format('M d, Y H:i') : 'N/A',
            'remarks' => $history->remarks ?? 'N/A',
            'borrowItems' => $borrowApprovalRecord->borrow_data['borrow_items'] ?? [],
            'returnItems' => $history->return_data['return_items'] ?? [],
        ];

        $pdf = Pdf::loadView('pdf.history-details', [
            'transaction' => (object)$transactionData,
            'borrowDate' => $transactionData['borrowed_at'],
            'returnDate' => $transactionData['returned_at']
        ]);
        
        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            "Asset-History-{$history->borrow_code}.pdf"
        );
    }



    private function sendDeletionEmail($history)
    {
        try {
            $emailService = new SendEmail();
            $user = Auth::user();
            
            $body = view('emails.history-deleted', [
                'history' => $history,
                'user' => $user
            ])->render();
            
            $emailService->send(
                $user->email,
                "History Deleted: {$history->borrow_code}",
                $body,
                [],
                null,
                null,
                true
            );
        } catch (\Exception $e) {
            Log::error("Deletion email failed: " . $e->getMessage());
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }

    public function canPrint($status)
    {
        return !in_array($status, ['Borrow Denied']);
    }
}