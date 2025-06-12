<?php

namespace App\Livewire\User;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetBorrowItem;
use App\Models\BorrowAssetQuantity;
use Illuminate\Support\Facades\Auth;

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
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('borrow_code', 'like', '%'.$this->search.'%')
                      ->orWhere('status', 'like', '%'.$this->search.'%')
                      ->orWhereDate('borrowed_at', 'like', '%'.$this->search.'%');
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
            
            if ($this->transactionToCancel->status !== 'Pending') {
                $this->errorMessage = 'Only pending requests can be cancelled!';
                $this->showCancelModal = false;
                return;
            }
            
            // Restore quantities
            foreach ($this->transactionToCancel->borrowItems as $item) {
                $assetQuantity = BorrowAssetQuantity::where('asset_id', $item->asset_id)->first();
                if ($assetQuantity) {
                    $assetQuantity->increment('available_quantity', $item->quantity);
                }
            }
            
            // Capture transaction code before deletion
            $borrowCode = $this->transactionToCancel->borrow_code;
            
            // Delete the transaction
            $this->transactionToCancel->delete();
            
            // Reset modals and selection
            if ($this->selectedTransaction && $this->selectedTransaction->id === $this->transactionToCancel->id) {
                $this->selectedTransaction = null;
                $this->showDetailsModal = false;
            }
            
            $this->successMessage = "Request $borrowCode cancelled successfully!";
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