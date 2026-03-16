<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // all roles
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(['admin', 'super_admin']) || $ticket->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole('super_admin');
    }
}
