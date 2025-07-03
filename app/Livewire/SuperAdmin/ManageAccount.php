<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Services\SendEmail;
use Carbon\Carbon;

class ManageAccount extends Component
{
    use WithPagination;

    public $search = '';
    public $confirmingUserDeletion = false;
    public $userIdToDelete = null;
    public $password = '';
    public $successMessage = '';
    
    public $confirmingStatusChange = false;
    public $userToChange = null;
    public $newStatus = null;
    public $statusMessage = '';
    public $originalStatus = null;
    public $userStatusMap = [];

    public function mount()
    {
        $this->initializeStatusMap();
    }

    protected function initializeStatusMap()
    {
        $users = User::where('id', '!=', Auth::id())
            ->get(['id', 'status']);
            
        foreach ($users as $user) {
            $this->userStatusMap[$user->id] = $user->status;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updateStatus($userId, $newStatus)
    {
        $user = User::findOrFail($userId);
        
        if($user->status === $newStatus) return;
        
        $this->userToChange = $user;
        $this->originalStatus = $user->status;
        $this->newStatus = $newStatus;
        $this->confirmingStatusChange = true;
        
        $this->statusMessage = match($newStatus) {
            'Approved' => "Do you really want to approve this user?",
            'Blocked' => "Do you really want to block this user?",
            'Pending' => "Do you really want to set this user to pending?",
            default => "Do you really want to change this user's status?"
        };
    }

    public function changeUserStatus()
    {
        $user = $this->userToChange;
        $oldStatus = $user->status;
        $user->status = $this->newStatus;
        $user->save();
        
        $this->userStatusMap[$user->id] = $this->newStatus;
        $this->dispatch('userStatusUpdated', $user->id);
        
        $this->sendStatusEmail($user, $oldStatus);
        
        $this->confirmingStatusChange = false;
        $this->successMessage = "User status updated to {$this->newStatus} successfully!";
        $this->clearSuccessMessage();
        
        $this->userToChange = null;
        $this->newStatus = null;
        $this->originalStatus = null;
    }

    public function cancelStatusChange()
    {
        if ($this->userToChange) {
            $this->userStatusMap[$this->userToChange->id] = $this->originalStatus;
            $this->dispatch('userStatusUpdated', $this->userToChange->id);
        }
        
        $this->confirmingStatusChange = false;
        $this->userToChange = null;
        $this->newStatus = null;
        $this->originalStatus = null;
    }

    protected function sendStatusEmail($user, $oldStatus)
    {
        $sendEmail = new SendEmail();
        $subject = "Account Status Update";
        
        $viewData = [
            'user' => $user,
            'oldStatus' => $oldStatus,
            'newStatus' => $this->newStatus,
            'time' => Carbon::now()->toDateTimeString(),
        ];
        
        $sendEmail->send(
            $user->email,
            $subject,
            ['emails.user-status-update', $viewData],
            [], 
            null, 
            null, 
            false 
        );
    }

    public function confirmDelete($userId)
    {
        $this->userIdToDelete = $userId;
        $this->confirmingUserDeletion = true;
        $this->resetErrorBag();
        $this->password = '';
        $this->successMessage = ''; 
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

        unset($this->userStatusMap[$this->userIdToDelete]);
        $this->dispatch('userDeleted', $this->userIdToDelete);
        
        $this->confirmingUserDeletion = false;
        $this->userIdToDelete = null;
        $this->password = '';
        $this->resetPage(); 
        
        $this->search = '';
        $this->successMessage = 'User deleted successfully!';
        $this->clearSuccessMessage();
    }

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
    
    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
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
            ->orderBy('id','DESC')
            ->paginate(10);
            
        foreach ($users as $user) {
            if (!isset($this->userStatusMap[$user->id])) {
                $this->userStatusMap[$user->id] = $user->status;
            }
        }

        return view('livewire.super-admin.manage-account', [
            'users' => $users,
        ]);
    }
}
