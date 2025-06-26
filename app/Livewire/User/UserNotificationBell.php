<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\UserHistory;
use Illuminate\Support\Facades\Auth;

class UserNotificationBell extends Component
{
    public $notifications = [];
    public $count = 0;
    public $isMarkingRead = false;

    protected $listeners = ['refreshNotifications' => 'refreshNotifications'];

    public function mount()
    {
        $this->refreshNotifications();
    }

    
    public function refreshNotifications()
    {
        $this->notifications = UserHistory::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->where(function ($query) {
                $query->where('status', 'Borrow Approved')
                    ->orWhere('status', 'Borrow Denied')
                    ->orWhere('status', 'Return Approved')
                    ->orWhere('status', 'Return Denied');
            })
            ->orderBy('action_date', 'desc')
            ->get()
            ->toArray();

        $this->count = count($this->notifications);
    }




    public function markAsRead($id)
    {
        $this->isMarkingRead = true;
        
        $notification = UserHistory::find($id);
        if ($notification && $notification->user_id == Auth::id()) {
            $notification->update(['read_at' => now()]);
            $this->refreshNotifications();
        }
        
        $this->isMarkingRead = false;
        return $id;
    }

    public function markAllAsRead()
    {
        $this->isMarkingRead = true;
        
        UserHistory::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        $this->refreshNotifications();
        $this->isMarkingRead = false;
    }

    public function getRoute($status)
    {
        switch($status) {
            case 'Borrow Approved':
            case 'Return Denied':
                return route('user.return.transactions');
            case 'Borrow Denied':
            case 'Return Approved':
                return route('user.history');
            default:
                return '#';
        }
    }

    public function render()
    {
        return view('livewire.user.user-notification-bell');
    }
}