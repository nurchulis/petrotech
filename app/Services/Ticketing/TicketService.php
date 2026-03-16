<?php

namespace App\Services\Ticketing;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketService
{
    public function list(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Ticket::with(['creator', 'assignedAdmin'])
            ->when(!$user->hasRole(['admin', 'super_admin']), fn($q) => $q->forUser($user))
            ->when($filters['status'] ?? null, fn($q, $s) => $q->byStatus($s))
            ->when($filters['priority'] ?? null, fn($q, $p) => $q->where('priority', $p))
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('title', 'ilike', "%{$s}%")
                ->orWhere('ticket_number', 'ilike', "%{$s}%"));

        return $query->latest()->paginate(15);
    }

    public function submit(array $data, User $user): Ticket
    {
        return Ticket::create(array_merge($data, ['created_by' => $user->id]));
    }

    public function assign(Ticket $ticket, int $adminId): Ticket
    {
        $ticket->update([
            'assigned_to' => $adminId,
            'status'      => 'in_progress',
        ]);
        return $ticket->fresh('assignedAdmin');
    }

    public function updateStatus(Ticket $ticket, string $status, ?string $notes = null): Ticket
    {
        $update = ['status' => $status];
        if ($notes) $update['resolution_notes'] = $notes;
        if ($status === 'resolved') $update['resolved_at'] = now();
        if ($status === 'closed')   $update['closed_at'] = now();

        $ticket->update($update);
        return $ticket->fresh();
    }

    public function statistics(): array
    {
        return [
            'open'        => Ticket::byStatus('open')->count(),
            'in_progress' => Ticket::byStatus('in_progress')->count(),
            'resolved'    => Ticket::byStatus('resolved')->count(),
            'closed'      => Ticket::byStatus('closed')->count(),
            'total'       => Ticket::count(),
        ];
    }
}
