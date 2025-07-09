<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserBorrowTransactions extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransaction = null;
    public $showDetailsModal = false;
    public $showCancelModal = false;
    public $transactionToCancel = null;
    public $successMessage = '';
    public $errorMessage = '';

    public function render()
    {
        $transactions = AssetBorrowTransaction::where('user_id', Auth::id())
            ->where('status', 'PendingBorrowApproval') 
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('borrow_code', 'like', '%'.$this->search.'%')
                    ->orWhereDate('created_at', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.user.user-borrow-transactions', [
            'transactions' => $transactions
        ]);
    }

    public function showDetails($transactionId)
    {
        $this->selectedTransaction = AssetBorrowTransaction::with('borrowItems.asset')
            ->findOrFail($transactionId);
        $this->showDetailsModal = true;
    }

    public function confirmCancel($transactionId)
    {
        $this->transactionToCancel = AssetBorrowTransaction::findOrFail($transactionId);
        $this->showCancelModal = true;
    }

    public function cancelRequest()
    {
        try {
            if (!$this->transactionToCancel) {
                $this->errorMessage = 'Invalid transaction!';
                return;
            }
            
            if ($this->transactionToCancel->status !== 'PendingBorrowApproval') {
                $this->errorMessage = 'Only pending requests can be cancelled!';
                $this->showCancelModal = false;
                return;
            }
            
            DB::transaction(function () {
                foreach ($this->transactionToCancel->borrowItems as $item) {
                    $asset = Asset::find($item->asset_id);
                    if ($asset) {
                        $asset->decrement('reserved_quantity', $item->quantity);
                    }
                }
                
                $this->transactionToCancel->delete();
            });
            
            $borrowCode = $this->transactionToCancel->borrow_code;
            
            if ($this->selectedTransaction && $this->selectedTransaction->id === $this->transactionToCancel->id) {
                $this->selectedTransaction = null;
                $this->showDetailsModal = false;
            }
            
            $this->successMessage = "Request $borrowCode cancelled successfully! Assets are now available.";
            $this->showCancelModal = false;
            $this->transactionToCancel = null;
            
        } catch (\Exception $e) {
            $this->errorMessage = "Failed to cancel request: " . $e->getMessage();
            $this->showCancelModal = false;
        }
    }

    public function clearMessages()
    {
        $this->reset(['successMessage', 'errorMessage']);
    }
}