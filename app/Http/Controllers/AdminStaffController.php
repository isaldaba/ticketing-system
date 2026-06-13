<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminStaffController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->query('period', 'month');
        $period = in_array($period, ['day', 'month', 'year', 'all'], true) ? $period : 'month';

        [$start, $end, $periodLabel] = $this->periodRange($period);
        $now = Carbon::now();
        $accomplishmentStart = $now->copy()->startOfMonth()->min($now->copy()->startOfWeek());

        $resolvedInPeriod = function ($query) use ($start, $end): void {
            $query->where('status', 'resolved')
                ->when($start && $end, fn ($query) => $query->whereBetween('resolved_at', [$start, $end]));
        };

        $staff = User::query()
            ->where('role', 'staff')
            ->with(['assignedTickets' => fn ($query) => $query
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$accomplishmentStart, $now->copy()->endOfDay()])
                ->latest('resolved_at')])
            ->withCount([
                'assignedTickets as total_taken',
                'assignedTickets as active_count' => fn ($query) => $query->where('status', 'in_progress'),
                'assignedTickets as pending_review_count' => fn ($query) => $query->where('status', 'pending_review'),
                'assignedTickets as resolved_count' => $resolvedInPeriod,
            ])
            ->orderByDesc('resolved_count')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_taken' => $user->total_taken,
                'active_count' => $user->active_count,
                'pending_review_count' => $user->pending_review_count,
                'resolved_count' => $user->resolved_count,
                'accomplishments' => $user->assignedTickets->map(fn (Ticket $ticket): array => [
                    'id' => $ticket->id,
                    'title' => $ticket->title,
                    'priority' => $ticket->priority,
                    'resolution_note' => $ticket->resolution_note,
                    'resolved_at' => $ticket->resolved_at?->format('M j, Y'),
                    'resolved_at_iso' => $ticket->resolved_at?->toIso8601String(),
                ])->values(),
                'charts' => [
                'progress' => [
                    ['label' => 'In Progress', 'value' => $user->active_count, 'color' => '#f59e0b'],
                    ['label' => 'For Review', 'value' => $user->pending_review_count, 'color' => '#6366f1'],
                    ['label' => 'Resolved', 'value' => $user->resolved_count, 'color' => '#22c55e'],
                ],
                    'priority' => collect(['critical', 'high', 'medium', 'low'])
                        ->map(fn (string $priority): array => [
                            'label' => ucfirst($priority),
                            'value' => Ticket::where('assigned_to', $user->id)
                                ->where($resolvedInPeriod)
                                ->where('priority', $priority)
                                ->count(),
                            'color' => [
                                'critical' => '#ef4444',
                                'high' => '#f97316',
                                'medium' => '#3b82f6',
                                'low' => '#64748b',
                            ][$priority],
                        ])
                        ->values(),
                ],
                'heatmap' => $this->resolvedHeatmap($user->id),
            ]);

        return Inertia::render('Admin/Staff', [
            'staff' => $staff,
            'filters' => [
                'period' => $period,
                'periodLabel' => $periodLabel,
            ],
            'summary' => [
                'staffCount' => User::where('role', 'staff')->count(),
                'resolvedCount' => Ticket::whereHas('assignee', fn (Builder $query) => $query->where('role', 'staff'))
                    ->where($resolvedInPeriod)
                    ->count(),
                'activeCount' => Ticket::where('status', 'in_progress')->count(),
                'pendingReviewCount' => Ticket::where('status', 'pending_review')->count(),
            ],
            'charts' => [
                'progress' => [
                    ['label' => 'In Progress', 'value' => Ticket::where('status', 'in_progress')->count(), 'color' => '#f59e0b'],
                    ['label' => 'For Review', 'value' => Ticket::where('status', 'pending_review')->count(), 'color' => '#6366f1'],
                    ['label' => 'Resolved', 'value' => Ticket::whereHas('assignee', fn (Builder $query) => $query->where('role', 'staff'))->where($resolvedInPeriod)->count(), 'color' => '#22c55e'],
                ],
                'priority' => collect(['critical', 'high', 'medium', 'low'])
                    ->map(fn (string $priority): array => [
                        'label' => ucfirst($priority),
                        'value' => Ticket::whereHas('assignee', fn (Builder $query) => $query->where('role', 'staff'))
                            ->where($resolvedInPeriod)
                            ->where('priority', $priority)
                            ->count(),
                        'color' => [
                            'critical' => '#ef4444',
                            'high' => '#f97316',
                            'medium' => '#3b82f6',
                            'low' => '#64748b',
                        ][$priority],
                    ])
                    ->values(),
            ],
            'heatmap' => $this->resolvedHeatmap(),
        ]);
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'day' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay(), 'Today'],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), 'This year'],
            'all' => [null, null, 'All time'],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), 'This month'],
        };
    }

    private function resolvedHeatmap(?int $staffId = null): array
    {
        $start = Carbon::today()->subYear()->addDay();
        $end = Carbon::today();

        $counts = Ticket::query()
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($staffId, fn (Builder $query) => $query->where('assigned_to', $staffId))
            ->selectRaw('date(resolved_at) as resolved_date, count(*) as total')
            ->groupBy('resolved_date')
            ->pluck('total', 'resolved_date');

        $days = collect(CarbonPeriod::create($start, $end))
            ->map(fn (Carbon $date): array => [
                'date' => $date->toDateString(),
                'label' => $date->format('M j, Y'),
                'count' => (int) ($counts[$date->toDateString()] ?? 0),
            ])
            ->values();

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'total' => $days->sum('count'),
            'days' => $days,
        ];
    }
}
