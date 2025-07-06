<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetReturnItem;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class AdminNotificationBell extends Component
{
    public $borrowCount = 0;
    public $returnCount = 0;
    public $pendingUserCount = 0;
    public $emailCount = 0;
    public $isSuperAdmin = false;
    public $isAdmin = false;

    protected $listeners = ['refreshCounts' => 'refreshCounts', 'refreshNotifications'];

    public function mount()
    {
        $user = Auth::user();
        $this->isSuperAdmin = $user && $user->role->name === 'Super Admin';
        $this->isAdmin = $user && $user->role->name === 'Admin';
        
        $this->refreshCounts();
    }

    public function refreshCounts()
    {
        $this->borrowCount = AssetBorrowTransaction::where('status', 'PendingBorrowApproval')->count();
        
        $this->returnCount = AssetReturnItem::where('approval_status', 'Pending')
            ->selectRaw('COUNT(DISTINCT return_code) as count')
            ->value('count') ?? 0;
            
        $this->pendingUserCount = $this->isSuperAdmin 
            ? User::where('status', 'Pending')->count()
            : 0;
            
        $this->emailCount = auth()->user()->unreadEmailNotifications()->count();
    }

    public function markEmailNotificationsAsRead()
    {
        auth()->user()->unreadEmailNotifications()
            ->update(['user_notifications.is_read' => true]);
        $this->emailCount = 0;
        $this->dispatch('refreshNotifications');
    }

    public function refreshNotifications()
    {
        $this->refreshCounts();
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
        return view('livewire.super-admin.admin-notification-bell');
    }
}