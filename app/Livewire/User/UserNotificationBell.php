<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\UserHistory;
use App\Models\Notification;
use App\Models\NotificationType;
use Illuminate\Support\Facades\Auth;

class UserNotificationBell extends Component
{
    public $userHistoryNotifications = [];
    public $emailNotifications = [];
    public $count = 0;
    public $isMarkingRead = false;
    public $userEmail;

    protected $listeners = ['refreshNotifications' => 'refreshNotifications'];

    public function mount()
    {
        $this->userEmail = Auth::user()->email;
        $this->refreshNotifications();
    }

    public function refreshNotifications()
    {
        $this->userHistoryNotifications = UserHistory::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->where(function ($query) {
                $query->where('status', 'Borrow Approved')
                    ->orWhere('status', 'Borrow Denied')
                    ->orWhere('status', 'Return Approved')
                    ->orWhere('status', 'Return Denied');
            })
            ->orderBy('action_date', 'desc')
            ->get()
            ->map(function ($item) {
                $notification = $item->toArray();
                $notification['type'] = 'history';  
                return $notification;
            })
            ->toArray();

        $this->emailNotifications = Auth::user()->unreadUserEmailNotifications()
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => 'email',
                    'message' => $notification->message,
                    'created_at' => $notification->created_at
                ];
            })
            ->toArray();

        $this->count = count($this->userHistoryNotifications) + count($this->emailNotifications);
    }


    public function markAsRead($id, $type = 'history')
    {
        $this->isMarkingRead = true;
        
        if ($type === 'history') {
            $notification = UserHistory::find($id);
            if ($notification && $notification->user_id == Auth::id()) {
                $notification->update(['read_at' => now()]);
            }
        } else {
            Auth::user()->notifications()->updateExistingPivot($id, ['is_read' => true]);
        }
        
        $this->refreshNotifications();
        $this->isMarkingRead = false;
        return $id;
    }

    public function markAllAsRead()
    {
        $this->isMarkingRead = true;
        
        UserHistory::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        Auth::user()->unreadUserEmailNotifications()->update(['user_notifications.is_read' => true]);
            
        $this->refreshNotifications();
        $this->isMarkingRead = false;
    }

    public function getRoute($notification)
    {
        if (!isset($notification['type'])) {
            return '#';  
        }

        if ($notification['type'] === 'email') {
            return $this->getEmailProviderUrl($this->userEmail);
        }
        
        switch($notification['status']) {
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
    
    public function getEmailProviderUrl($email)
    {
        $domain = substr(strrchr($email, "@"), 1);
        
        return match($domain) {
            'gmail.com' => 'https://mail.google.com',
            'yahoo.com' => 'https://mail.yahoo.com',
            'outlook.com', 'hotmail.com' => 'https://outlook.live.com',
            'icloud.com' => 'https://www.icloud.com/mail',
            default => 'https://' . $domain,
        };
    }

    public function render()
    {
        return view('livewire.user.user-notification-bell');
    }
}