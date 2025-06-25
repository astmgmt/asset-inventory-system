<?php

namespace App\Livewire\AccountProfile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\TracksUserActivities;
use Illuminate\Http\Request; 

class EditProfile extends Component
{
    use WithFileUploads, TracksUserActivities;

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
        
        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->contact_number = $this->user->contact_number;
        $this->address = $this->user->address;
    }

    public function updatedProfilePhoto()
    {
        $this->validate([
            'profile_photo' => 'image|max:2048', 
        ]);
        
        $this->temp_profile_photo = $this->profile_photo->temporaryUrl();
    }

    public function updateProfile()
    {

        $originalEmail = $this->user->email;
        $passwordChanged = !empty($this->new_password);

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user->id,
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
        ]);
        
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

        if ($this->profile_photo) {
            if ($this->user->profile_photo_path) {
                Storage::delete('public/' . $this->user->profile_photo_path);
            }
            
            $path = $this->profile_photo->store('profile-photos', 'public');
            $this->user->profile_photo_path = $path;
        }

        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'profile_photo_path' => $this->user->profile_photo_path,
        ];

        if ($this->new_password) {
            $updateData['password'] = Hash::make($this->new_password);
        }

        $this->user->update($updateData);

        $request = request(); 
        $additionalEmails = [];
        
        if ($originalEmail !== $this->email) {
            $additionalEmails = [$originalEmail, $this->email];
            
            $this->recordActivity(
                'Email Updated',
                "You changed your email from $originalEmail to {$this->email}",
                $request,
                true, 
                $additionalEmails
            );
        }
        
        if ($passwordChanged) {
            $this->recordActivity(
                'Password Changed',
                'You changed your password',
                $request,
                true 
            );
        }

        $this->successMessage = 'Profile updated successfully!';
        $this->profile_photo = null;
        $this->temp_profile_photo = null;
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        
        $this->dispatch('clear-message');
        
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