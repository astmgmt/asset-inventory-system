<?php

namespace App\Livewire\User;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetReturnItem;
use App\Models\UserHistory;

class UserContainers extends Component
{
    public string $activeTab = 'borrow';
    public int $pendingBorrowCount = 0;
    public int $pendingReturnPendingCount = 0;
    public int $pendingReturnRejectedCount = 0;
    public int $pendingReturnBorrowedCount = 0;
    public int $borrowApprovedHistoryCount = 0;
    public int $borrowDeniedHistoryCount = 0;
    public int $returnApprovedHistoryCount = 0;


    protected $listeners = ['refreshCounts' => 'refreshCounts'];

    public function mount()
    {
        $this->refreshCounts();
    }

    public function refreshCounts()
    {
        $user = Auth::user();
        if ($user) {
            $this->pendingBorrowCount = AssetBorrowTransaction::where('user_id', $user->id)
                ->where('status', 'PendingBorrowApproval')
                ->count();

            $this->pendingReturnPendingCount = AssetReturnItem::where('returned_by_user_id', $user->id)
                ->where('approval_status', 'Pending')
                ->distinct('return_code')
                ->count();

            $this->pendingReturnRejectedCount = AssetReturnItem::where('returned_by_user_id', $user->id)
                ->where('approval_status', 'Rejected')
                ->count();

            $this->pendingReturnBorrowedCount = AssetBorrowTransaction::where('user_id', $user->id)
                ->where('status', 'Borrowed')
                ->distinct('borrow_code')
                ->count();

            $this->borrowApprovedHistoryCount = UserHistory::where('user_id', $user->id)
                ->where('status', 'Borrow Approved')
                ->count();

            $this->borrowDeniedHistoryCount = UserHistory::where('user_id', $user->id)
                ->where('status', 'Borrow Denied')
                ->count();

            $this->returnApprovedHistoryCount = UserHistory::where('user_id', $user->id)
                ->where('status', 'Return Approved')
                ->count();

            
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.user.user-containers');
    }
}