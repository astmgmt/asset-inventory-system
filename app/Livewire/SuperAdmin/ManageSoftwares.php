<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Software;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ManageSoftwares extends Component
{
    use WithPagination;

    public $search = '';
    
    // Software fields
    public $softwareId;
    public $software_name;
    public $description;
    public $license_key;
    public $expiry_date;
    
    // Modals
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showDeleteModal = false;
    
    // View software
    public $viewSoftware;
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';

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
            'expiry_date'
        ]);
        $this->resetErrorBag();
        $this->viewSoftware = null;
    }

    private function generateSoftwareCode()
    {
        $date = now()->format('Ymd'); // e.g., 20250622
        $prefix = "SFT-{$date}-";

        // Find the last software created today
        $lastSoftware = Software::where('software_code', 'like', $prefix . '%')
            ->orderBy('software_code', 'desc')
            ->first();

        // Extract the last sequence number
        $lastNum = 0;
        if ($lastSoftware) {
            $lastCode = $lastSoftware->software_code;
            $sequencePart = substr($lastCode, -8); // Get last 8 characters
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
        ]);

        Software::create([
            'software_code' => $this->generateSoftwareCode(),
            'software_name' => $this->software_name,
            'description' => $this->description,
            'license_key' => $this->license_key,
            'expiry_date' => $this->expiry_date,
            'added_by' => Auth::id(),
            'quantity' => 1,
            'reserved_quantity' => 0,
            'expiry_flag' => false,
            'expiry_status' => 'active',
        ]);

        $this->successMessage = 'Software created successfully!';
        $this->closeModals();
        $this->dispatch('clear-message');
    }

    public function updateSoftware()
    {
        $this->validate([
            'software_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'license_key' => 'required|string|max:100',
            'expiry_date' => 'required|date',            
        ]);

        $software = Software::findOrFail($this->softwareId);
        $software->update([
            'software_name' => $this->software_name,
            'description' => $this->description,
            'license_key' => $this->license_key,
            'expiry_date' => $this->expiry_date,
            
        ]);

        $this->successMessage = 'Software updated successfully!';
        $this->closeModals();
        $this->dispatch('clear-message');
    }

    public function deleteSoftware()
    {
        $software = Software::findOrFail($this->softwareId);
        $software->delete();
        
        $this->successMessage = 'Software deleted successfully!';
        $this->closeModals();
        $this->dispatch('clear-message');
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