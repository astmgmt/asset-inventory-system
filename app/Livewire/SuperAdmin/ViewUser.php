<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class ViewUser extends Component
{
    public $user;
    public $userDetails = [];

    public function mount($id)
    {
        $this->user = User::with('role')->findOrFail($id);
        
        $this->userDetails = [
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'role' => $this->user->role->name ?? 'N/A',
            'status' => $this->user->status,
            'contact_number' => $this->user->contact_number ?? 'N/A',
            'address' => $this->user->address ?? 'N/A',
            'created_at' => $this->user->created_at->format('F j, Y, g:i a'),
            'profile_photo' => $this->user->profile_photo_path 
                ? Storage::url($this->user->profile_photo_path) 
                : asset('images/default-profile.jpg')
        ];
    }

    public function closeView()
    {
        return redirect()->route('superadmin.manage');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.super-admin.view-user');
    }
}