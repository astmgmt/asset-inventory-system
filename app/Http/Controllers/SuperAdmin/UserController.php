<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
   
    public function index()
    {
        //$users = User::with('role')->get();
        $users = User::where('id', '!=', auth()->id())->get();
        return view('superadmin.manage', compact('users'));
    }
   
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Blocked'])],
        ]);

        $user->status = $request->status;
        $user->save();

        return redirect()->route('superadmin.manage')->with('success', 'User status updated.');
    }
    
    public function show(User $user)
    {
        $user->load('role');
        return view('superadmin.manage_show', compact('user'));
    }
   
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('superadmin.manage_edit', compact('user', 'roles'));
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update($request->only(['name', 'username', 'email', 'role_id']));

        return redirect()->route('superadmin.manage')->with('success', 'User updated successfully.');
    }
    
    public function destroy(Request $request, User $user)
    {
        // Make sure the currently logged-in Super Admin is entering the correct password
        $superAdmin = Auth::user();

        // Validate input
        $request->validate([
            'password' => 'required|string',
        ]);

        // Check if the password matches
        if (!Hash::check($request->password, $superAdmin->password)) {
            // Incorrect password, redirect back with error message
            return redirect()->route('superadmin.manage')
                ->with('error', 'Incorrect password. Try again.');
        }

        // Prevent deleting the currently logged-in user
        if ($user->id === $superAdmin->id) {
            return redirect()->route('superadmin.manage')
                ->with('error', 'You cannot delete your own account.');
        }
       
        $user->delete();
        
        return redirect()->route('superadmin.manage')
            ->with('success', 'User deleted successfully.');
    }

}
