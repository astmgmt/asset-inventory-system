<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
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

    // Department properties
    public $departmentSearch = '';
    public $departmentSuggestions = [];
    public $showDepartmentDropdown = false;
    public $originalDepartmentName = '';

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
        
        // Initialize department properties
        $this->departmentSearch = $this->user->department->name ?? '';
        $this->originalDepartmentName = $this->departmentSearch;
    }

    public function updatedDepartmentSearch($value)
    {
        $this->showDepartmentDropdown = !empty($value);
        
        if (empty($value)) {
            $this->departmentSuggestions = [];
            return;
        }
        
        $this->departmentSuggestions = Department::where('name', 'like', '%'.$value.'%')
            ->pluck('name')
            ->take(5)
            ->toArray();
    }

    public function selectDepartment($departmentName)
    {
        $this->departmentSearch = $departmentName;
        $this->departmentSuggestions = [];
        $this->showDepartmentDropdown = false;
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
            'departmentSearch' => 'nullable|string|max:255',
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

        // Resolve department ID from search input
        $departmentId = null;
        $newDepartmentName = trim($this->departmentSearch);
        
        if (!empty($newDepartmentName)) {
            $department = Department::firstOrCreate(
                ['name' => $newDepartmentName],
                ['description' => 'Added through user update']
            );
            $departmentId = $department->id;
        }

        // Log department resolution
        \Log::debug('Department resolution', [
            'input' => $this->departmentSearch,
            'resolved_id' => $departmentId,
            'new_name' => $newDepartmentName
        ]);

        // Capture changes BEFORE updating
        $changes = [];
        $fields = [
            'name', 
            'username', 
            'email', 
            'role_id', 
            'contact_number', 
            'address',
        ];
        
        foreach ($fields as $field) {
            $originalValue = $this->originalData[$field] ?? null;
            $newValue = $this->{$field};
            
            // Compare values with proper null handling
            if ($originalValue != $newValue) {
                if ($field === 'role_id') {
                    $oldRole = Role::find($originalValue)->name ?? 'N/A';
                    $newRole = Role::find($newValue)->name ?? 'N/A';
                    $changes['role'] = ['old' => $oldRole, 'new' => $newRole];
                } else {
                    $changes[$field] = [
                        'old' => $originalValue ?? 'N/A',
                        'new' => $newValue ?? 'N/A'
                    ];
                }
            }
        }

        // Handle department change
        $oldDepartmentName = $this->originalDepartmentName;
        if ($oldDepartmentName !== $newDepartmentName) {
            $changes['department'] = [
                'old' => $oldDepartmentName ?: 'N/A',
                'new' => $newDepartmentName ?: 'N/A'
            ];
        }

        // Handle profile photo upload
        $profilePhotoPath = $this->user->profile_photo_path;
        if ($this->profile_photo) {
            if ($this->user->profile_photo_path) {
                Storage::delete('public/' . $this->user->profile_photo_path);
            }
            
            $path = $this->profile_photo->store('profile-photos', 'public');
            $profilePhotoPath = $path;
            
            // Add photo change to notification
            $changes['profile_photo'] = [
                'old' => $this->originalData['profile_photo_path'] ? 'Existing Photo' : 'No Photo',
                'new' => 'New Photo Uploaded'
            ];
        }

        // Update user data - USE DIRECT DB QUERY TO ENSURE UPDATE
        try {
            $updateData = [
                'name' => $this->name,
                'username' => $this->username,
                'email' => $this->email,
                'role_id' => $this->role_id,
                'contact_number' => $this->contact_number,
                'address' => $this->address,
                'profile_photo_path' => $profilePhotoPath,
                'department_id' => $departmentId,
            ];
            
            // Log update data
            \Log::debug('Updating user data', $updateData);
            
            // Use direct DB update to bypass any model issues
            $updated = User::where('id', $this->user->id)->update($updateData);
            
            if ($updated) {
                \Log::info('User updated successfully', ['user_id' => $this->user->id]);
            } else {
                \Log::error('User update failed', ['user_id' => $this->user->id]);
                $this->errorMessage = 'Failed to update user record';
                return;
            }
        } catch (\Exception $e) {
            \Log::error('User update exception: '.$e->getMessage());
            $this->errorMessage = 'Error updating user: '.$e->getMessage();
            return;
        }

        // Send email notification if changes occurred
        if (!empty($changes)) {
            try {
                // Refresh user data for email
                $freshUser = User::find($this->user->id);
                
                Mail::to($freshUser->email)->send(
                    new UserUpdatedNotification($freshUser, $changes)
                );
                
                \Log::info('User update email sent', [
                    'user_id' => $this->user->id,
                    'email' => $freshUser->email,
                    'changes' => $changes
                ]);
            } catch (\Exception $e) {
                \Log::error('Email sending failed: ' . $e->getMessage(), [
                    'user_id' => $this->user->id,
                    'exception' => $e
                ]);
                
                // Queue email if possible
                if (config('queue.default') !== 'sync') {
                    try {
                        Mail::to($freshUser->email)->queue(
                            new UserUpdatedNotification($freshUser, $changes)
                        );
                        \Log::info('Queued user update email after send failure');
                    } catch (\Exception $qe) {
                        \Log::error('Email queuing failed: ' . $qe->getMessage());
                    }
                }
            }
        }

        $this->successMessage = 'User updated successfully!';
        $this->showPasswordModal = false;
        $this->superAdminPassword = '';
        $this->profile_photo = null;
        $this->temp_profile_photo = null;
        
        // Refresh user data
        $this->user->refresh();
        $this->originalData = $this->user->toArray();
        $this->originalDepartmentName = $this->user->department->name ?? '';
        
        // Log final state
        \Log::debug('User after update', [
            'department_id' => $this->user->department_id,
            'department_name' => $this->user->department->name ?? null
        ]);
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