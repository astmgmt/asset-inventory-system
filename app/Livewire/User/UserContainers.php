<?php

namespace App\Livewire\User;

use Livewire\Component;

class UserContainers extends Component
{
    public string $activeTab = 'borrow';

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.user.user-containers');
    }
}

