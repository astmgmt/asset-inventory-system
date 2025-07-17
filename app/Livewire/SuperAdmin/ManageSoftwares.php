<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Software;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ManageSoftwares extends Component
{
    use WithPagination;

    public $search = '';
    
    public $softwareId;
    public $software_name;
    public $description;
    public $license_key;
    public $date_acquired;
    public $expiry_date;
    public $quantity = 1;
    
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    
    public $viewSoftware;
    
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->expiry_date = now()->addYear()->format('Y-m-d');
        $this->date_acquired = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    public function openEditModal($id)
    {
        $software = Software::findOrFail($id);
        
        $this->softwareId = $software->id;
        $this->software_name = $software->software_name;
        $this->description = $software->description;
        $this->license_key = $software->license_key;
        $this->expiry_date = $software->expiry_date->format('Y-m-d');
        $this->date_acquired = $software->date_acquired->format('Y-m-d');

        $this->quantity = $software->quantity;
        
        $this->showEditModal = true;
    }
    
    public function openViewModal($id)
    {
        $this->viewSoftware = Software::findOrFail($id);
        $this->showViewModal = true;
    }

    public function confirmDelete($id)
    {
        $this->softwareId = $id;
        $this->showDeleteModal = true;
    }

    public function closeModals()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'softwareId', 'software_name', 'description', 'license_key', 
            'expiry_date', 'date_acquired', 'quantity'
        ]);
        $this->resetErrorBag();
        $this->viewSoftware = null;

        $this->expiry_date = now()->addYear()->format('Y-m-d');
        $this->date_acquired = now()->format('Y-m-d');
    }

    private function generateSoftwareCode()
    {
        $date = now()->format('Ymd'); 
        $prefix = "SFT-{$date}-";

        $lastSoftware = Software::withTrashed()
            ->where('software_code', 'like', $prefix . '%')
            ->orderBy('software_code', 'desc')
            ->first();

        $lastNum = 0;
        if ($lastSoftware) {
            $lastCode = $lastSoftware->software_code;
            $sequencePart = substr($lastCode, -8); 
            $lastNum = intval($sequencePart);
        }

        $nextNum = $lastNum + 1;
        $formattedNum = str_pad($nextNum, 8, '0', STR_PAD_LEFT);

        return $prefix . $formattedNum;
    }

    public function createSoftware()
    {
        $this->validate([
            'software_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'license_key' => 'required|string|max:100',
            'expiry_date' => 'required|date',
            'date_acquired' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
        ]);

        $expiryDate = Carbon::parse($this->expiry_date);
        $isExpired = $expiryDate->isPast();
        
        $assignStatus = $isExpired ? 'Unavailable' : 'Available';
        $expiryStatus = $isExpired ? 'expired' : 'active';
        $expiryFlag = $isExpired;

        try {
            Software::create([
                'software_code' => $this->generateSoftwareCode(),
                'software_name' => $this->software_name,
                'description' => $this->description,
                'license_key' => $this->license_key,
                'expiry_date' => $this->expiry_date,
                'date_acquired' => $this->date_acquired ?? now()->format('Y-m-d'),
                'added_by' => Auth::id(),
                'quantity' => $this->quantity,
                'reserved_quantity' => 0,
                'expiry_flag' => $expiryFlag,
                'expiry_status' => $expiryStatus,
                'assign_status' => $assignStatus,
            ]);

            $this->successMessage = 'Software created successfully!';
            $this->closeModals();
        } catch (\Exception $e) {
            Log::error('Software creation failed: ' . $e->getMessage());
            $this->errorMessage = 'Error creating software. Please check database configuration.';
        }
    }

    public function updateSoftware()
    {
        $this->validate([
            'software_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'license_key' => 'required|string|max:100',
            'expiry_date' => 'required|date',
            'date_acquired' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
        ]);

        $software = Software::findOrFail($this->softwareId);
        
        $expiryDate = Carbon::parse($this->expiry_date);
        $isExpired = $expiryDate->isPast();
        
        $assignStatus = $isExpired ? 'Unavailable' : 'Available';
        $expiryStatus = $isExpired ? 'expired' : 'active';
        $expiryFlag = $isExpired ? 1 : 0; 
        $showStatus = $isExpired ? 0 : 1; 

        try {
            $software->update([
                'software_name' => $this->software_name,
                'description' => $this->description,
                'license_key' => $this->license_key,
                'expiry_date' => $this->expiry_date,
                'date_acquired' => $this->date_acquired,
                'quantity' => $this->quantity,
                'expiry_flag' => $expiryFlag,
                'expiry_status' => $expiryStatus,
                'assign_status' => $assignStatus,
                'show_status' => $showStatus, 
            ]);

            $this->successMessage = 'Software updated successfully!';
            $this->closeModals();
        } catch (\Exception $e) {
            Log::error('Software update failed: ' . $e->getMessage());
            $this->errorMessage = 'Error updating software. Please check database configuration.';
        }
    }

    public function deleteSoftware()
    {
        $software = Software::findOrFail($this->softwareId);
        $software->delete();
        
        $this->successMessage = 'Software deleted successfully!';
        $this->closeModals();
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function render()
    {
        $softwares = Software::query()
            ->where(function ($query) {
                $query->where('software_name', 'like', '%' . $this->search . '%')
                    ->orWhere('software_code', 'like', '%' . $this->search . '%')
                    ->orWhere('license_key', 'like', '%' . $this->search . '%');                     
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.manage-softwares', [
            'softwares' => $softwares,
        ]);
    }
}
