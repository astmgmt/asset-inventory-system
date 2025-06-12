<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use App\Traits\AuthorizesAdmins;
use Illuminate\Support\Facades\Auth;

class BorrowRequests extends Component
{
    use AuthorizesAdmins;

    public function mount()
    {
        if (!$this->isAuthorizedAdmin()) {
            Auth::logout();
            session()->flash('error', 'Access denied. Please log in as Admin or Super Admin.');
            $this->redirectRoute('login');
        }
    }

    public function render()
    {
        return view('livewire.super-admin.borrow-requests');
    }
}


