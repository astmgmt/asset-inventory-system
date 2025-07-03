<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetReturnItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminNotificationBell extends Component
{
    public $borrowCount = 0;
    public $returnCount = 0;
    public $pendingUserCount = 0;
    public $isSuperAdmin = false;
    public $isAdmin = false;

    protected $listeners = ['refreshCounts' => 'refreshCounts'];

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
    }

    public function render()
    {
        return view('livewire.super-admin.admin-notification-bell');
    }
}