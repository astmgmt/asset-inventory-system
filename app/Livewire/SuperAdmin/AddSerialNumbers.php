<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\Asset;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;

class AddSerialNumbers extends Component
{
    use WithPagination;

    public $showModal = false;
    public $showContinueModal = false;
    public $assets = [];
    public $serialNumbers = [];
    public $perPage = 10;
    public $nullSerialsCount = 0;
    public $currentPage = 1;
    public $totalPages = 1;

    public function mount()
    {
        $this->updateCounts();
    }

    protected function updateCounts()
    {
        $this->nullSerialsCount = Asset::whereNull('serial_number')->count();
        $this->totalPages = max(1, ceil($this->nullSerialsCount / $this->perPage));
    }

    public function loadAssets()
    {
        $this->assets = Asset::whereNull('serial_number')
            ->orderBy('asset_code')
            ->skip(($this->currentPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get()
            ->toArray();
        
        $this->serialNumbers = array_fill(0, count($this->assets), null);
    }

    public function openModal()
    {
        $this->currentPage = 1;
        $this->updateCounts();
        $this->loadAssets();
        $this->showModal = true;
        $this->showContinueModal = false;
    }

    public function saveSerials()
    {
        $this->validate([
            'serialNumbers.*' => 'nullable|string|max:20|unique:assets,serial_number',
        ]);

        foreach ($this->assets as $index => $assetData) {
            if (!empty($this->serialNumbers[$index])) {
                $asset = Asset::find($assetData['id']);
                $asset->update(['serial_number' => $this->serialNumbers[$index]]);
            }
        }

        $this->updateCounts();
        $this->showModal = false;
        
        if ($this->nullSerialsCount > 0) {
            $this->showContinueModal = true;
        } else {
            Session::flash('message', 'All serial numbers have been updated!');
            $this->dispatch('refresh-parent');
        }
    }

    public function continueAdding()
    {
        $this->currentPage = 1; 
        $this->updateCounts();
        $this->loadAssets();
        $this->showContinueModal = false;
        $this->showModal = true;
    }

    public function closeAll()
    {
        $this->resetValidationErrors();
        $this->showModal = false;
        $this->showContinueModal = false;
        Session::flash('message', 'Serial numbers updated successfully!');
        $this->dispatch('refresh-parent');
    }

    public function resetValidationErrors()
    {
        $this->resetErrorBag();
        $this->resetValidation();
    }
    public function closeModal()
    {
        $this->resetValidationErrors();
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.super-admin.add-serial-numbers');
    }
}