<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Asset;
use App\Models\Software;

#[Layout('components.layouts.app')]
class SuperAdminDashboard extends Component
{
    use WithPagination;

    public $assetCounts = [];
    public $softwareCounts = [];

    protected $listeners = ['pollChartData'];
 
    public function mount()
    {
        $this->refreshExpiryStatuses();
        $this->loadCounts();
    }

    public function pollChartData()
    {
        $this->refreshExpiryStatuses();
        $this->loadCounts();

        $this->dispatchBrowserEvent('chartDataUpdated', [
            'assetCounts' => $this->assetCounts,
            'softwareCounts' => $this->softwareCounts,
        ]);
    }

    protected function refreshExpiryStatuses()
    {
        Asset::all()->each(fn ($asset) => $asset->updateExpiryStatus());
        Software::all()->each(fn ($software) => $software->updateExpiryStatus());
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

    /**
     * Paginated expiring assets query.
     */
    public function getExpiringAssetsProperty()
    {
        return Asset::whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m'])
            ->orderBy('warranty_expiration', 'asc')
            ->paginate(5); // Adjust the number as you want
    }

    /**
     * Paginated expiring software query.
     */
    public function getExpiringSoftwareProperty()
    {
        return Software::whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m'])
            ->orderBy('expiry_date', 'asc')
            ->paginate(5);
    }

    public function render()
    {
        return view('livewire.dashboard.super-admin-dashboard', [
            'expiringAssets' => $this->expiringAssets,
            'expiringSoftware' => $this->expiringSoftware,
        ]);
    }

    // Because we use computed properties, add these accessors:

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

}
