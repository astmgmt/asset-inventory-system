<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Mail\UserUpdatedNotification;

class EditUser extends Component
{
    use WithFileUploads;

    public $user;
    public $name;
    public $username;
    public $email;
    public $role_id;
    public $contact_number;
    public $address;
    public $profile_photo;
    public $temp_profile_photo;
    public $password;
    
    public $showPasswordModal = false;
    public $superAdminPassword = '';
    public $successMessage = '';
    public $errorMessage = '';
    public $roles;
    public $originalData = [];

    public function mount($id)
    {
        $this->user = User::findOrFail($id);
        
        // Prevent editing own account
        if ($this->user->id == Auth::id()) {
            abort(403, 'You cannot edit your own account');
        }
        
        // Set initial values
        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->role_id = $this->user->role_id;
        $this->contact_number = $this->user->contact_number;
        $this->address = $this->user->address;
        
        // Store original data for change detection
        $this->originalData = $this->user->toArray();
        
        $this->roles = Role::all();
    }

    public function updatedProfilePhoto()
    {
        $this->validate([
            'profile_photo' => 'image|max:2048', // 2MB Max
        ]);
        
        $this->temp_profile_photo = $this->profile_photo->temporaryUrl();
    }

    public function openPasswordModal()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'role_id' => 'required|exists:roles,id',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        $this->showPasswordModal = true;
    }

    public function updateUser()
    {
        // Verify super admin password
        if (!Hash::check($this->superAdminPassword, Auth::user()->password)) {
            $this->addError('superAdminPassword', 'Incorrect password');
            return;
        }

        // Handle profile photo upload
        if ($this->profile_photo) {
            // Delete old photo if exists
            if ($this->user->profile_photo_path) {
                Storage::delete('public/' . $this->user->profile_photo_path);
            }
            
            // Store new photo
            $path = $this->profile_photo->store('profile-photos', 'public');
            $this->user->profile_photo_path = $path;
        }

        // Update user data
        $this->user->update([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'profile_photo_path' => $this->user->profile_photo_path,
        ]);

        // Prepare changed data for email
        $changes = [];
        $fields = ['name', 'username', 'email', 'role_id', 'contact_number', 'address'];
        
        foreach ($fields as $field) {
            if ($this->originalData[$field] !== $this->user->$field) {
                $oldValue = $this->originalData[$field];
                $newValue = $this->user->$field;
                
                // Handle role ID to name conversion
                if ($field === 'role_id') {
                    $oldRole = Role::find($oldValue)->name ?? 'N/A';
                    $newRole = Role::find($newValue)->name ?? 'N/A';
                    $changes['role'] = ['old' => $oldRole, 'new' => $newRole];
                } else {
                    $changes[$field] = [
                        'old' => $oldValue ?? 'N/A',
                        'new' => $newValue ?? 'N/A'
                    ];
                }
            }
        }

        // Add profile photo change if updated
        if ($this->profile_photo) {
            $changes['profile_photo'] = [
                'old' => $this->originalData['profile_photo_path'] ? 'Existing Photo' : 'No Photo',
                'new' => 'New Photo Uploaded'
            ];
        }

        // Send email notification if changes occurred
        if (!empty($changes)) {
            try {
                Mail::to($this->user->email)->send(
                    new UserUpdatedNotification($this->user, $changes)
                );
            } catch (\Exception $e) {
                logger()->error('Email sending failed: ' . $e->getMessage());
            }
        }

        $this->successMessage = 'User updated successfully!';
        $this->showPasswordModal = false;
        $this->superAdminPassword = '';
        $this->profile_photo = null;
        $this->temp_profile_photo = null;
        
        // Refresh original data
        $this->user->refresh();
        $this->originalData = $this->user->toArray();
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->resetErrorBag();
        $this->superAdminPassword = '';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.super-admin.edit-user');
    }
}