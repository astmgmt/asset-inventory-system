<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller 
{
    public function showForm()
    {
        $roles = Role::all();
        return view('superadmin.register-new-account', compact('roles'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['required', 'in:Approved,Pending,Blocked'],
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'contact_number' => $validated['contact_number'],
                'address' => $validated['address'],
                'role_id' => $validated['role_id'],
                'status' => $validated['status'],
            ]);

            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $user->profile_photo_path = $path;
                $user->save();
            }

            return back()->with('success', 'Account created successfully!');
            
        } catch (\Exception $e) {
            Log::error('Account creation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to create account. Please try again.');
        }
    }
}
