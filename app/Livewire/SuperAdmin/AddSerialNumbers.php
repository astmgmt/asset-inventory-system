<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Models\Asset;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

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
    public $fieldErrors = [];

    protected $listeners = [
        'checkDuplicates' => 'checkForDuplicates',
        'refresh-serial-count' => 'refreshCount' 
    ];

    public function refreshCount()
    {
        $this->nullSerialsCount = Asset::whereNull('serial_number')->count();
        $this->totalPages = max(1, ceil($this->nullSerialsCount / $this->perPage));
    }

    public function mount()
    {
        $this->refreshCount(); 
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
        $this->fieldErrors = array_fill(0, count($this->assets), null);
    }

    public function openModal()
    {
        $this->currentPage = 1;
        $this->refreshCount();
        $this->loadAssets();
        $this->showModal = true;
        $this->showContinueModal = false;
    }

    public function saveSerials()
    {
        if (empty(array_filter($this->serialNumbers))) {
            $this->addError('emptyFields', 'Please fill up at least one serial number or click Cancel to close.');
            return;
        }

        $this->validate([
            'serialNumbers.*' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && Asset::where('serial_number', $value)->exists()) {
                        $fail('This serial number has already been taken.');
                    }
                },
            ],
        ], [
            'serialNumbers.*.max' => 'Serial number cannot exceed 20 characters',
        ]);

        $this->checkForBatchDuplicates();

        if (!empty(array_filter($this->fieldErrors))) {
            return;
        }

        try {
            $updatedCount = 0;
            foreach ($this->assets as $index => $assetData) {
                if (!empty($this->serialNumbers[$index])) {
                    $asset = Asset::find($assetData['id']);
                    $asset->update(['serial_number' => $this->serialNumbers[$index]]);
                    $updatedCount++;
                }
            }

            $this->refreshCount();
            $this->showModal = false;
            
            if ($updatedCount > 0) {
                if ($this->nullSerialsCount > 0) {
                    $this->showContinueModal = true;
                } else {
                    Session::flash('message', 'Serial numbers updated successfully!');
                    $this->dispatch('refresh-parent');
                }
            }
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $this->addError('databaseDuplicate', 'One or more serial numbers already exist in the system. Please use unique values.');
        } catch (\Exception $e) {
            $this->addError('generalError', 'An error occurred while saving the serial numbers.');
        }
    }

    protected function checkForBatchDuplicates()
    {
        $this->fieldErrors = array_fill(0, count($this->assets), null);
        
        $counts = array_count_values(array_filter($this->serialNumbers));
        $duplicates = array_filter($counts, fn($count) => $count > 1);
        
        if (!empty($duplicates)) {
            foreach ($this->serialNumbers as $index => $serial) {
                if (!empty($serial) && in_array($serial, array_keys($duplicates))) {
                    $this->fieldErrors[$index] = 'Duplicate serial number in this batch';
                }
            }
        }
    }

    public function checkForDuplicates()
    {
        $this->validate([
            'serialNumbers.*' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && Asset::where('serial_number', $value)->exists()) {
                        $fail('This serial number has already been taken.');
                    }
                },
            ],
        ]);
        
        $this->checkForBatchDuplicates();
        $this->dispatch('duplicates-checked');
    }

    public function continueAdding()
    {
        $this->currentPage = 1; 
        $this->refreshCount();
        $this->loadAssets();
        $this->showContinueModal = false;
        $this->showModal = true;
    }

    

    public function closeAll()
    {
        $this->refreshCount();
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