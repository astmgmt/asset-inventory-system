<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\UserHistory;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Auth;

class UserDashboard extends Component
{
    public $borrowedCount = 0;
    public $returnedCount = 0;
    public $recentLogs = [];

    protected $listeners = ['updateChartData'];

    public function mount()
    {
        $this->updateStats();
    }

    public function updateStats()
    {
        $userId = Auth::id();

        $this->borrowedCount = UserHistory::where('user_id', $userId)
            ->where('status', 'Borrow Approved')
            ->count();

        $this->returnedCount = UserHistory::where('user_id', $userId)
            ->where('status', 'Return Approved')
            ->count();

        $this->recentLogs = UserHistory::where('user_id', $userId)
            ->whereIn('status', [
                'Borrow Approved', 
                'Borrow Denied',
                'Return Approved',
                'Return Denied'
            ])
            ->latest('action_date')
            ->take(5)
            ->get();

        $this->dispatch('chartDataUpdated', [
            'borrowed' => $this->borrowedCount,
            'returned' => $this->returnedCount
        ]);
    }

    public function getRecentActivitiesProperty()
    {
        return UserActivity::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();
    }
    
    public function updateChartData()
    {
        $this->updateStats();
    }

    public function render()
    {
        return view('livewire.dashboard.user-dashboard', [
            'recentActivities' => $this->recentActivities,
        ]);
    }
} 