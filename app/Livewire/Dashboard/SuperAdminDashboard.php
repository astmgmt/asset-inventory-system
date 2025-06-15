<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Asset;
use App\Models\Software;

#[Layout('components.layouts.app')]
class SuperAdminDashboard extends Component
{
    public $expiringAssets = [];
    public $expiringSoftware = [];
    public $assetCounts = [];
    public $softwareCounts = [];

    public function fetchData()
    {
        $this->expiringAssets = $this->getExpiringAssets();
        $this->expiringSoftware = $this->getExpiringSoftware();

        $newAssetCounts = $this->getAssetCounts();
        $newSoftwareCounts = $this->getSoftwareCounts();

        if ($newAssetCounts != $this->assetCounts || $newSoftwareCounts != $this->softwareCounts) {
            $this->assetCounts = $newAssetCounts;
            $this->softwareCounts = $newSoftwareCounts;

            $this->dispatch('chartDataUpdated', [
                'assetCounts' => $this->assetCounts,
                'softwareCounts' => $this->softwareCounts
            ]);
        }
    }


    public function mount()
    {
        $this->expiringAssets = $this->getExpiringAssets();
        $this->expiringSoftware = $this->getExpiringSoftware();
        $this->assetCounts = $this->getAssetCounts();
        $this->softwareCounts = $this->getSoftwareCounts();
    }


    public function getExpiringAssets()
    {
        return Asset::whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m'])
            ->orderBy('warranty_expiration', 'asc')
            ->get()
            ->each(fn ($asset) => $asset->updateExpiryStatus());
    }

    public function getExpiringSoftware()
    {
        return Software::whereIn('expiry_status', ['warning_3m', 'warning_2m', 'warning_1m'])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->each(fn ($software) => $software->updateExpiryStatus());
    }

    public function getAssetCounts()
    {
        return [
            '3m' => Asset::where('expiry_status', 'warning_3m')->count(),
            '2m' => Asset::where('expiry_status', 'warning_2m')->count(),
            '1m' => Asset::where('expiry_status', 'warning_1m')->count(),
        ];
    }

    public function getSoftwareCounts()
    {
        return [
            '3m' => Software::where('expiry_status', 'warning_3m')->count(),
            '2m' => Software::where('expiry_status', 'warning_2m')->count(),
            '1m' => Software::where('expiry_status', 'warning_1m')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.super-admin-dashboard');
    }
}