<?php

namespace App\Livewire\AccountProfile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProfile extends Component
{
    use WithFileUploads;

    public $user;
    public $name;
    public $username;
    public $email;
    public $contact_number;
    public $address;
    public $profile_photo;
    public $temp_profile_photo;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->user = Auth::user();
        
        // Set initial values
        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->contact_number = $this->user->contact_number;
        $this->address = $this->user->address;
    }

    public function updatedProfilePhoto()
    {
        $this->validate([
            'profile_photo' => 'image|max:2048', // 2MB Max
        ]);
        
        $this->temp_profile_photo = $this->profile_photo->temporaryUrl();
    }

    public function updateProfile()
    {
        // Validate all fields
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
        // Validate password if changing password
        $passwordRules = [];
        if ($this->new_password) {
            $passwordRules = [
                'current_password' => ['required', function ($attribute, $value, $fail) {
                    if (!Hash::check($value, $this->user->password)) {
                        $fail('The current password is incorrect.');
                    }
                }],
                'new_password' => 'required|min:8|confirmed',
            ];
            $this->validate($passwordRules);
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

        // Prepare update data
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'profile_photo_path' => $this->user->profile_photo_path,
        ];

        // Update password if provided
        if ($this->new_password) {
            $updateData['password'] = Hash::make($this->new_password);
        }

        // Update user data
        $this->user->update($updateData);

        $this->successMessage = 'Profile updated successfully!';
        $this->profile_photo = null;
        $this->temp_profile_photo = null;
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        
        // Clear success message after 3 seconds
        $this->dispatch('clear-message');
        
        // Refresh user data
        $this->user->refresh();
    }

    public function clearSuccessMessage()
    {
        $this->successMessage = '';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.account-profile.edit-profile', [
            'currentPhoto' => $this->user->profile_photo_path 
                ? Storage::url($this->user->profile_photo_path) 
                : asset('images/default-profile.jpg')
        ]);
    }
}