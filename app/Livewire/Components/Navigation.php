<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Navigation extends Component
{
    public $isAuthenticated = false;
    public $user = null;
    public $membershipTier = null;
    public $mobileMenuOpen = false;

    public function mount()
    {
        $this->isAuthenticated = Auth::check();

        if ($this->isAuthenticated) {
            $this->user = Auth::user();
            $this->membershipTier = $this->user->membership_tier;
        }
    }

    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }

    public function logout()
    {
        Auth::logout();
        $this->redirect('/');
    }

    public function render()
    {
        return view('livewire.navigation');
    }
}
