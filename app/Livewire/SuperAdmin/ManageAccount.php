<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ManageAccount extends Component
{
    public $search = '';
    public $confirmingUserDeletion = false;
    public $userIdToDelete = null;
    public $password = '';

    public function updateStatus($userId, $newStatus)
    {
        $user = User::findOrFail($userId);
        $user->status = $newStatus;
        $user->save();
    }

    public function confirmDelete($userId)
    {
        $this->userIdToDelete = $userId;
        $this->confirmingUserDeletion = true;
        $this->resetErrorBag();
        $this->password = '';
    }

    public function deleteUser()
    {
        $superAdmin = Auth::user();

        if (!Hash::check($this->password, $superAdmin->password)) {
            $this->addError('password', 'Incorrect password.');
            return;
        }

        User::findOrFail($this->userIdToDelete)->delete();

        $this->confirmingUserDeletion = false;
        $this->userIdToDelete = null;
        $this->password = '';
    }

    public function viewUser($userId)
    {
        return redirect()->route('users.view', ['id' => $userId]);
    }

    public function editUser($userId)
    {
        return redirect()->route('users.edit', ['id' => $userId]);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $users = User::with('role')
            ->where('id', '!=', Auth::id())
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id')
            ->get();

        return view('livewire.super-admin.manage-account', [
            'users' => $users,
        ]);
    }
}
