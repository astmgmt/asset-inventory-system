<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use App\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;    

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {      
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'contact_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        //HANDLE PROFILE PHOTO UPLOAD
        $profilePhotoPath = null;

        if (isset($input['profile_photo']) && $input['profile_photo'] instanceof UploadedFile) {
            $profilePhotoPath = $input['profile_photo']->store('profile-photos', 'public');
        }

        // ADDED CODE TO ASSIGN DEFAULT USER ROLE
        // Get the 'User' role ID
        $defaultRole = Role::where('name', 'User')->first();

        return User::create([
            'name' => $input['name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'contact_number' => $input['contact_number'],
            'address' => $input['address'],
            'profile_photo_path' => $profilePhotoPath,
            'role_id' => $defaultRole->id, // Set the default role
            'password' => Hash::make($input['password']),
        ]);
    }
}
