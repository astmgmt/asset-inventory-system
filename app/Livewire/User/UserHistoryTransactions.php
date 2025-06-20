<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\UserHistory;
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
}