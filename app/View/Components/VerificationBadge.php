<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\User;

class VerificationBadge extends Component
{
    public User $user;
    public string $size;

    /**
     * Create a new component instance.
     */
    public function __construct(User $user, string $size = '14px')
    {
        $this->user = $user;
        $this->size = $size;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.verification-badge');
    }
}