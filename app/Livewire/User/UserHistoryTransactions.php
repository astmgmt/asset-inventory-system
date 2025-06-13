<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\UserHistory;
use App\Services\SendEmail;
use Illuminate\Support\Facades\Auth;

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
        $history = \App\Models\UserHistory::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('borrow_code', 'like', '%'.$this->search.'%')
                    ->orWhere('return_code', 'like', '%'.$this->search.'%')
                    ->orWhere('status', 'like', '%'.$this->search.'%');
                });
            })
            ->select(
                \DB::raw('MAX(id) as id'), // important!
                'borrow_code',
                \DB::raw('MIN(return_code) as return_code'),
                \DB::raw('MIN(status) as status'),
                \DB::raw('MIN(action_date) as action_date')
            )
            ->groupBy('borrow_code')
            ->orderBy('action_date', 'desc')
            ->paginate(10);

        return view('livewire.user.user-history-transactions', [
            'history' => $history
        ]);
    }



    public function showDetails($historyId)
    {
        $this->selectedHistory = UserHistory::findOrFail($historyId);
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
            $borrowCode = $this->selectedHistory->borrow_code;

            // Optionally send email for each record
            $historyItems = UserHistory::where('borrow_code', $borrowCode)->get();
            foreach ($historyItems as $item) {
                $this->sendDeletionEmail($item);
            }

            // Delete all records with the same borrow_code
            UserHistory::where('borrow_code', $borrowCode)->delete();

            $this->successMessage = "All records with Borrow Code {$borrowCode} were deleted successfully!";
            $this->reset(['showDeleteModal', 'selectedHistory']);
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to delete history: " . $e->getMessage();
        }
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