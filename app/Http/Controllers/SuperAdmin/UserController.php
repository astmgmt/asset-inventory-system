<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of user accounts.
     */
    public function index()
    {
        $users = User::with('role')->get();
        return view('superadmin.manage', compact('users'));
    }

    /**
     * Update the user status (Approved, Blocked, Pending).
     */
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Blocked'])],
        ]);

        $user->status = $request->status;
        $user->save();

        return redirect()->route('superadmin.manage')->with('success', 'User status updated.');
    }

    /**
     * Show detailed information about a user.
     */
    public function show(User $user)
    {
        $user->load('role');
        return view('superadmin.manage_show', compact('user'));
    }

    /**
     * Show the form for editing a user account.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('superadmin.manage_edit', compact('user', 'roles'));
    }

    /**
     * Update the user's profile data.
     */
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

    /**
     * Delete a user account with password confirmation.
     */
    public function destroy(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user->delete();

        return redirect()->route('superadmin.manage')->with('success', 'User deleted successfully.');
    }
}
