<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Software;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class ManageSoftwares extends Component
{
    use WithPagination;

    public $search = '';
    public $users;
    
    // Software fields
    public $softwareId;
    public $software_name;
    public $description;
    public $license_key;
    public $installation_date;
    public $expiry_date;
    public $responsible_user_id;
    
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

    public function mount()
    {
        $this->users = User::all();
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
        $this->installation_date = $software->installation_date->format('Y-m-d');
        $this->expiry_date = $software->expiry_date->format('Y-m-d');
        $this->responsible_user_id = $software->responsible_user_id;
        
        $this->showEditModal = true;
    }
    
    public function openViewModal($id)
    {
        $this->viewSoftware = Software::with('responsibleUser')
            ->findOrFail($id);
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
            'installation_date', 'expiry_date', 'responsible_user_id'
        ]);
        $this->resetErrorBag();
        $this->viewSoftware = null;
    }

    private function generateSoftwareCode()
    {
        return implode('-', [
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4)),
            Str::upper(Str::random(4))
        ]);
    }

    public function createSoftware()
    {
        $this->validate([
            'software_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'license_key' => 'required|string|max:100',
            'installation_date' => 'required|date',
            'expiry_date' => 'required|date|after:installation_date',
            'responsible_user_id' => 'required|exists:users,id',
        ]);

        Software::create([
            'software_code' => $this->generateSoftwareCode(),
            'software_name' => $this->software_name,
            'description' => $this->description,
            'license_key' => $this->license_key,
            'installation_date' => $this->installation_date,
            'expiry_date' => $this->expiry_date,
            'responsible_user_id' => $this->responsible_user_id,
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
            'installation_date' => 'required|date',
            'expiry_date' => 'required|date|after:installation_date',
            'responsible_user_id' => 'required|exists:users,id',
        ]);

        $software = Software::findOrFail($this->softwareId);
        $software->update([
            'software_name' => $this->software_name,
            'description' => $this->description,
            'license_key' => $this->license_key,
            'installation_date' => $this->installation_date,
            'expiry_date' => $this->expiry_date,
            'responsible_user_id' => $this->responsible_user_id,
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

    public function clearSuccessMessage()
    {
        $this->dispatch('clear-message');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $softwares = Software::with('responsibleUser')
            ->where(function ($query) {
                $query->where('software_name', 'like', '%' . $this->search . '%')
                      ->orWhere('software_code', 'like', '%' . $this->search . '%')
                      ->orWhere('license_key', 'like', '%' . $this->search . '%')
                      ->orWhereHas('responsibleUser', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.super-admin.manage-softwares', [
            'softwares' => $softwares,
        ]);
    }
}