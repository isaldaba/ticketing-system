<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StaffTicketController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user = $request->user();

        $availableTickets = Ticket::query()
            ->where('status', 'open')
            ->whereNull('assigned_to')
            ->orderByRaw("case priority when 'critical' then 1 when 'high' then 2 when 'medium' then 3 else 4 end")
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket));

        $myTickets = Ticket::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['in_progress', 'pending_review', 'resolved'])
            ->orderByRaw("case status when 'in_progress' then 1 when 'pending_review' then 2 else 3 end")
            ->latest()
            ->get()
            ->map(fn (Ticket $ticket): array => $this->serializeTicket($ticket));

        return Inertia::render('Staff/Dashboard', [
            'availableTickets' => $availableTickets,
            'myTickets' => $myTickets,
            'stats' => [
                'available' => Ticket::where('status', 'open')->whereNull('assigned_to')->count(),
                'inProgress' => Ticket::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
                'pendingReview' => Ticket::where('assigned_to', $user->id)->where('status', 'pending_review')->count(),
                'resolved' => Ticket::where('assigned_to', $user->id)->where('status', 'resolved')->count(),
            ],
        ]);
    }

    public function claim(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->status !== 'open' || $ticket->assigned_to !== null) {
            return back()->with('error', 'This ticket is no longer available.');
        }

        $ticket->update([
            'assigned_to' => $request->user()->id,
            'status' => 'in_progress',
        ]);

        return back()->with('success', 'Ticket picked up.');
    }

    public function submitForReview(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->assigned_to !== $request->user()->id || $ticket->status !== 'in_progress') {
            abort(403);
        }

        $validated = $request->validate([
            'resolution_note' => ['required', 'string', 'max:3000'],
            'user_remarks' => ['nullable', 'string', 'max:3000'],
            'resolution_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $resolutionImagePath = $ticket->resolution_image_path;

        if ($request->hasFile('resolution_image')) {
            if ($resolutionImagePath) {
                Storage::disk('public')->delete($resolutionImagePath);
            }

            $resolutionImagePath = $request->file('resolution_image')->store('ticket-resolution-images', 'public');
        }

        $ticket->update([
            'resolution_note' => $validated['resolution_note'],
            'resolution_image_path' => $resolutionImagePath,
            'user_remarks' => $validated['user_remarks'] ?? null,
            'status' => 'pending_review',
            'submitted_at' => now(),
            'admin_review_seen_at' => null,
        ]);

        return back()->with('success', 'Ticket submitted for admin review.');
    }

    public function markReturnNotificationRead(Request $request, Ticket $ticket): RedirectResponse
    {
        if ($ticket->assigned_to !== $request->user()->id) {
            abort(403);
        }

        $ticket->update([
            'staff_return_seen_at' => now(),
        ]);

        return redirect()->route('staff.dashboard');
    }

    private function serializeTicket(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'title' => $ticket->title,
            'requester_name' => $ticket->requester_name,
            'requester_email' => $ticket->requester_email,
            'concern' => $ticket->concern,
            'resolution_note' => $ticket->resolution_note,
            'resolution_image_url' => $ticket->resolution_image_path ? Storage::url($ticket->resolution_image_path) : null,
            'user_remarks' => $ticket->user_remarks,
            'admin_note' => $ticket->admin_note,
            'priority' => $ticket->priority,
            'due_date' => $ticket->due_date?->format('M j, Y'),
            'due_date_iso' => $ticket->due_date?->copy()->endOfDay()->toIso8601String(),
            'status' => $ticket->status,
            'submitted_at' => $ticket->submitted_at?->diffForHumans(),
            'submitted_at_label' => $ticket->submitted_at?->format('M j, Y g:i A'),
            'resolved_at' => $ticket->resolved_at?->diffForHumans(),
            'resolved_at_label' => $ticket->resolved_at?->format('M j, Y g:i A'),
            'resolved_at_iso' => $ticket->resolved_at?->toIso8601String(),
            'created_at' => $ticket->created_at?->diffForHumans(),
            'created_at_label' => $ticket->created_at?->format('M j, Y g:i A'),
            'created_at_iso' => $ticket->created_at?->toIso8601String(),
        ];
    }
}
