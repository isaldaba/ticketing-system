<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'notifications' => $this->notifications($request),
        ];
    }

    private function notifications(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [
                'count' => 0,
                'items' => [],
            ];
        }

        if ($user->role === 'admin') {
            $tickets = Ticket::query()
                ->with('assignee')
                ->where('status', 'pending_review')
                ->whereNull('admin_review_seen_at')
                ->latest('submitted_at')
                ->take(5)
                ->get();

            return [
                'count' => Ticket::where('status', 'pending_review')
                    ->whereNull('admin_review_seen_at')
                    ->count(),
                'items' => $tickets->map(fn (Ticket $ticket): array => [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'message' => ($ticket->assignee?->name ?? 'A staff member').' submitted a ticket for review.',
                    'time' => $ticket->submitted_at?->diffForHumans() ?? $ticket->updated_at?->diffForHumans(),
                    'href' => route('admin.tickets.notifications.review.read', $ticket),
                    'method' => 'post',
                ]),
            ];
        }

        $tickets = Ticket::query()
            ->where('assigned_to', $user->id)
            ->where('status', 'in_progress')
            ->whereNotNull('admin_note')
            ->whereNull('staff_return_seen_at')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return [
            'count' => Ticket::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->whereNotNull('admin_note')
                ->whereNull('staff_return_seen_at')
                ->count(),
            'items' => $tickets->map(fn (Ticket $ticket): array => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'message' => 'Admin returned this ticket as unresolved.',
                'time' => $ticket->updated_at?->diffForHumans(),
                'href' => route('staff.tickets.notifications.return.read', $ticket),
                'method' => 'post',
            ]),
        ];
    }
}
