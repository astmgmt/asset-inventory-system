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
        //$this->updateStats();
    }

    public function updateStats()
    {
        $userId = Auth::id();

        $this->borrowedCount = UserHistory::where('user_id', $userId)
            ->whereIn('status', ['Borrow Approved', 'Borrow Denied'])
            ->count();

        $this->returnedCount = UserHistory::where('user_id', $userId)
            ->whereIn('status', ['Return Approved', 'Return Denied'])
            ->count();

        $this->recentLogs = UserHistory::where('user_id', $userId)
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
            ->paginate(5);
    }

    public function render()
    {
        $this->updateStats();
        return view('livewire.dashboard.user-dashboard', [
            'recentActivities' => $this->recentActivities,
        ]);
    }
}
