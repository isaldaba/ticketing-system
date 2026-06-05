<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'open');

        $tickets = Ticket::query()
            ->with('assignee')
            ->when(in_array($status, ['open', 'in_progress', 'pending_review', 'resolved'], true), fn ($query) => $query->where('status', $status))
            ->orderByRaw("case priority when 'critical' then 1 when 'high' then 2 when 'medium' then 3 else 4 end")
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket));

        return Inertia::render('Admin/Tickets', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
            ],
            'counts' => [
                'all' => Ticket::count(),
                'open' => Ticket::where('status', 'open')->count(),
                'inProgress' => Ticket::where('status', 'in_progress')->count(),
                'pendingReview' => Ticket::where('status', 'pending_review')->count(),
                'resolved' => Ticket::where('status', 'resolved')->count(),
            ],
        ]);
    }

    public function dashboard(): Response
    {
        $recentTickets = Ticket::query()
            ->with('assignee')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket));

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total' => Ticket::count(),
                'open' => Ticket::where('status', 'open')->count(),
                'pendingReview' => Ticket::where('status', 'pending_review')->count(),
                'resolved' => Ticket::where('status', 'resolved')->count(),
                'highPriority' => Ticket::whereIn('priority', ['high', 'critical'])->count(),
            ],
            'recentTickets' => $recentTickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'requester_name' => ['required', 'string', 'max:120'],
            'requester_email' => ['nullable', 'email', 'max:255'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'concern' => ['required', 'string', 'max:5000'],
        ]);

        Ticket::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'status' => 'open',
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Ticket created.');
    }

    public function approve(Ticket $ticket): RedirectResponse
    {
        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Ticket resolved.');
    }

    public function returnToUser(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        if (! $ticket->assigned_to) {
            return back()->with('error', 'This ticket has no assigned user.');
        }

        $ticket->update([
            'status' => 'in_progress',
            'admin_note' => $validated['admin_note'],
            'submitted_at' => null,
        ]);

        return back()->with('success', 'Ticket returned to user.');
    }

    private function serializeTicket(Ticket $ticket): array
    {
        $openFor = $ticket->resolved_at
            ? $ticket->created_at?->diffForHumans($ticket->resolved_at, true)
            : $ticket->created_at?->diffForHumans(now(), true);

        return [
            'id' => $ticket->id,
            'title' => $ticket->title,
            'requester_name' => $ticket->requester_name,
            'requester_email' => $ticket->requester_email,
            'concern' => $ticket->concern,
            'resolution_note' => $ticket->resolution_note,
            'user_remarks' => $ticket->user_remarks,
            'admin_note' => $ticket->admin_note,
            'priority' => $ticket->priority,
            'due_date' => $ticket->due_date?->format('M j, Y'),
            'status' => $ticket->status,
            'assignee_name' => $ticket->assignee?->name,
            'open_for' => $openFor,
            'submitted_at' => $ticket->submitted_at?->diffForHumans(),
            'resolved_at' => $ticket->resolved_at?->diffForHumans(),
            'created_at' => $ticket->created_at?->diffForHumans(),
        ];
    }
}
