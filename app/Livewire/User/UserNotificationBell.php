<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetReturnItem;
use Illuminate\Support\Facades\Auth;

class UserNotificationBell extends Component
{
    public $notificationCount = 0;
    public $recentNotifications = [];

    protected $listeners = ['refreshNotifications' => 'refreshNotifications'];

    public function mount()
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications()
    {
        $user = Auth::user();
        $threeDaysAgo = now()->subDays(3);

        // Get borrow status updates
        $borrowUpdates = AssetBorrowTransaction::where('user_id', $user->id)
            ->whereIn('status', ['Borrowed', 'BorrowRejected'])
            ->where('updated_at', '>', $threeDaysAgo)
            ->select('id', 'borrow_code', 'status', 'updated_at')
            ->get()
            ->map(function ($transaction) {
                return [
                    'type' => 'borrow',
                    'status' => $transaction->status,
                    'code' => $transaction->borrow_code,
                    'time' => $transaction->updated_at,
                    'message' => $this->getStatusMessage($transaction->status, $transaction->borrow_code)
                ];
            });

        // Get return status updates
        $returnUpdates = AssetReturnItem::where('returned_by_user_id', $user->id)
            ->whereIn('approval_status', ['Approved', 'Rejected'])
            ->where('updated_at', '>', $threeDaysAgo)
            ->with('borrowItem.borrowTransaction')
            ->get()
            ->map(function ($returnItem) {
                return [
                    'type' => 'return',
                    'status' => $returnItem->approval_status,
                    'code' => $returnItem->borrowItem->borrowTransaction->borrow_code,
                    'time' => $returnItem->updated_at,
                    'message' => $this->getStatusMessage($returnItem->approval_status.'Return', $returnItem->borrowItem->borrowTransaction->borrow_code)
                ];
            });

        $this->recentNotifications = $borrowUpdates->concat($returnUpdates)
            ->sortByDesc('time')
            ->take(5)
            ->values();

        $this->notificationCount = $this->recentNotifications->count();
    }

    private function getStatusMessage($status, $code)
    {
        $messages = [
            'Borrowed' => "Borrow Approved: Your request #$code has been approved",
            'BorrowRejected' => "Borrow Rejected: Your request #$code was declined",
            'ApprovedReturn' => "Return Approved: Your return for #$code was accepted",
            'RejectedReturn' => "Return Rejected: Your return for #$code was declined",
        ];

        return $messages[$status] ?? "Status update for request #$code";
    }

    public function render()
    {
        return view('livewire.user.user-notification-bell');
    }
}