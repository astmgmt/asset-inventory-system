<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Asset;
use App\Models\Software;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class SuperAdminDashboard extends Component
{
    use WithPagination;

    public $assetCounts = [];
    public $softwareCounts = [];
    public $user;

    protected $listeners = ['pollChartData'];

    protected function getPageName() 
    { 
        return 'assetsPage'; 
    }
    public function getSoftwarePageName() 
    { 
        return 'softwarePage'; 
    }
 
    public function mount()
    {
        $this->user = Auth::user();
        $this->refreshExpiryStatuses();
        $this->loadCounts();
    }

    public function pollChartData()
    {
        $this->refreshExpiryStatuses();
        $this->loadCounts();

        $this->dispatch('chartDataUpdated', [
            'assetCounts' => $this->assetCounts,
            'softwareCounts' => $this->softwareCounts,
        ]);
    }

    protected function refreshExpiryStatuses()
    {
        Asset::chunk(100, fn($assets) => $assets->each->updateExpiryStatus());
        Software::chunk(100, fn($software) => $software->each->updateExpiryStatus());
    }

    protected function loadCounts()
    {
        $this->assetCounts = $this->getAssetCounts();
        $this->softwareCounts = $this->getSoftwareCounts();
    }

    protected function getAssetCounts()
    {
        return [
            '3m' => Asset::where('expiry_status', 'warning_3m')->count(),
            '2m' => Asset::where('expiry_status', 'warning_2m')->count(),
            '1m' => Asset::where('expiry_status', 'warning_1m')->count(),
        ];
    }

    protected function getSoftwareCounts()
    {
        return [
            '3m' => Software::where('expiry_status', 'warning_3m')->count(),
            '2m' => Software::where('expiry_status', 'warning_2m')->count(),
            '1m' => Software::where('expiry_status', 'warning_1m')->count(),
        ];
    }

    public function getExpiringAssetsProperty()
    {
        return Asset::whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m', 'expired'])
            ->where('show_status', 1)
            ->orderBy('warranty_expiration', 'asc')
            ->paginate(5, ['*'], $this->getPageName());  
    }

    public function getExpiringSoftwareProperty()
    {
        return Software::whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m', 'expired'])
            ->where('show_status', 1)
            ->orderBy('expiry_date', 'asc')
            ->paginate(5, ['*'], $this->getSoftwarePageName());
    }

    public function removeAsset($id)
    {
        $asset = Asset::find($id);
        if ($asset && $asset->expiry_status === 'expired') {
            $asset->update(['show_status' => 0]);
            $this->pollChartData();
        }
    }

    public function removeSoftware($id)
    {
        $software = Software::find($id);
        if ($software && $software->expiry_status === 'expired') {
            $software->update(['show_status' => 0]);
            $this->pollChartData();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.super-admin-dashboard', [
            'expiringAssets' => $this->expiringAssets,
            'expiringSoftware' => $this->expiringSoftware,
            'hasExpiredAssets' => $this->hasExpiredAssets,
            'hasExpiredSoftware' => $this->hasExpiredSoftware,
            'user' => $this->user,
        ]);
    }

    public function getExpiringAssets()
    {
        return $this->expiringAssetsProperty;
    }

    public function getExpiringSoftware()
    {
        return $this->expiringSoftwareProperty;
    }
    public function updatedPage()
    {
        $this->dispatch('chartDataUpdated', [
            'assetCounts' => $this->assetCounts,
            'softwareCounts' => $this->softwareCounts,
        ]);
    }
    public function getHasExpiredAssetsProperty()
    {
        return Asset::where('expiry_status', 'expired')
            ->where('show_status', 1)
            ->exists();
    }

    public function getHasExpiredSoftwareProperty()
    {
        return Software::where('expiry_status', 'expired')
            ->where('show_status', 1)
            ->exists();
    }

}
