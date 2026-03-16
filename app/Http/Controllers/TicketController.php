<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\Ticketing\TicketService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function __construct(private TicketService $service) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'priority', 'search']);
        $tickets = $this->service->list(auth()->user(), $filters);
        $stats   = $this->service->statistics();
        return view('tickets.index', compact('tickets', 'stats'));
    }

    public function create(): View
    {
        return view('tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:500',
            'description' => 'required|string',
            'category'    => 'required|string|max:100',
            'priority'    => 'required|in:low,medium,high,critical',
            'attachment'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('tickets', 'public');
        }
        unset($data['attachment']);

        $this->service->submit($data, auth()->user());
        return redirect()->route('tickets.index')->with('success', 'Ticket submitted successfully.');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);
        $ticket->load(['creator', 'assignedAdmin', 'comments.user']);
        $admins = User::role(['admin', 'super_admin'])->get();
        return view('tickets.show', compact('ticket', 'admins'));
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);
        $request->validate(['admin_id' => 'required|exists:users,id']);
        $this->service->assign($ticket, $request->admin_id);
        return back()->with('success', 'Ticket assigned.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);
        $data = $request->validate([
            'status'           => 'required|in:open,in_progress,resolved,closed',
            'resolution_notes' => 'nullable|string',
        ]);
        $this->service->updateStatus($ticket, $data['status'], $data['resolution_notes'] ?? null);
        return back()->with('success', 'Ticket status updated.');
    }

    public function comment(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('view', $ticket);
        $data = $request->validate([
            'body'            => 'required|string',
            'is_internal_note'=> 'nullable|boolean',
            'attachment'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('ticket-comments', 'public');
        }
        unset($data['attachment']);

        TicketComment::create(array_merge($data, [
            'ticket_id' => $ticket->id,
            'user_id'   => auth()->id(),
            'is_internal_note' => $data['is_internal_note'] ?? false,
        ]));

        return back()->with('success', 'Reply added.');
    }
}
