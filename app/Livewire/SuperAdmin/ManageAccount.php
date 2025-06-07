<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class ManageAccount extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingUserDeletion = false;
    public $userIdToDelete = null;
    public $password = '';
    public $successMessage = ''; // For success notification

    // Reset pagination when search changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

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
        $this->successMessage = ''; // Clear any previous messages
    }

    public function deleteUser()
    {
        $superAdmin = Auth::user();

        if (!Hash::check($this->password, $superAdmin->password)) {
            $this->addError('password', 'Incorrect password.');
            return;
        }

        $user = User::findOrFail($this->userIdToDelete);
        $user->delete();

        $this->confirmingUserDeletion = false;
        $this->userIdToDelete = null;
        $this->password = '';
        $this->resetPage(); // Reset pagination after deletion
        
        // Reset search and show success message
        $this->search = '';
        $this->successMessage = 'User deleted successfully!';
        $this->clearSuccessMessage();
    }

    // Clear success message after 3 seconds
    public function clearSuccessMessage()
    {
        $this->dispatch('clear-message');
    }

    public function viewUser($userId)
    {
        return redirect()->route('superadmin.manage.view_account', ['id' => $userId]);
    }

    public function editUser($userId)
    {
        return redirect()->route('superadmin.manage.edit_account', ['id' => $userId]);
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
            ->paginate(10);

        return view('livewire.super-admin.manage-account', [
            'users' => $users,
        ]);
    }
}