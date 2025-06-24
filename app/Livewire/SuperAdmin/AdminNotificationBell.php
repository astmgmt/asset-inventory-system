<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\AssetBorrowTransaction;
use App\Models\AssetReturnItem;

class AdminNotificationBell extends Component
{
    public $borrowCount = 0;
    public $returnCount = 0;

    protected $listeners = ['refreshCounts' => 'refreshCounts'];

    public function mount()
    {
        $this->refreshCounts();
    }

    public function refreshCounts()
    {
        $this->borrowCount = AssetBorrowTransaction::where('status', 'PendingBorrowApproval')->count();
        
        $this->returnCount = AssetReturnItem::where('approval_status', 'Pending')
            ->selectRaw('COUNT(DISTINCT return_code) as count')
            ->value('count') ?? 0;
    }

    public function render()
    {
        return view('livewire.super-admin.admin-notification-bell');
    }
}