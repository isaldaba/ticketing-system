<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AccomplishmentReportController extends Controller
{
    public function staff(Request $request): Response
    {
        return $this->stream($request, $request->user());
    }

    public function admin(Request $request, User $user): Response
    {
        abort_unless($user->role === 'staff', 404);

        return $this->stream($request, $user);
    }

    public function adminMulti(Request $request): Response
    {
        $request->validate([
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['integer', 'exists:users,id'],
        ]);

        $staffMembers = User::whereIn('id', $request->input('users'))
            ->where('role', 'staff')
            ->get();

        abort_unless($staffMembers->count(), 404);

        $period = $request->string('period')->value();
        $period = in_array($period, ['week', 'last_week', 'month'], true) ? $period : 'week';
        $now = Carbon::now();

        if ($period === 'last_week') {
            $start = $now->copy()->startOfWeek()->subWeek();
            $end = $now->copy()->startOfWeek()->subWeek()->addDays(6)->endOfDay();
        } elseif ($period === 'month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfDay();
        } else {
            $start = $now->copy()->startOfWeek();
            $end = $now->isSunday()
                ? $now->copy()->startOfWeek()->addDays(5)->endOfDay()
                : $now->copy()->endOfDay();
        }

        $groupedTickets = [];
        $groupedPendingTickets = [];
        $totalTicketCount = 0;
        $totalPendingCount = 0;

        foreach ($staffMembers as $staff) {
            $tickets = Ticket::query()
                ->where('assigned_to', $staff->id)
                ->where('status', 'resolved')
                ->whereBetween('resolved_at', [$start, $end])
                ->orderBy('resolved_at')
                ->get();

            $pendingTickets = Ticket::query()
                ->where('assigned_to', $staff->id)
                ->whereIn('status', ['in_progress', 'pending_review'])
                ->orderBy('created_at')
                ->get();

            $groupedTickets[$staff->id] = $tickets;
            $groupedPendingTickets[$staff->id] = $pendingTickets;
            $totalTicketCount += $tickets->count();
            $totalPendingCount += $pendingTickets->count();
        }

        $periodLabel = match ($period) {
            'last_week' => 'Last Week',
            'month' => 'This Month',
            default => 'This Week',
        };
        $filename = sprintf(
            'accomplishment-report-%d-staff-%s-%s.pdf',
            $staffMembers->count(),
            $period,
            $now->format('Y-m-d'),
        );

        return Pdf::loadView('reports.accomplishment', [
            'staffMembers' => $staffMembers,
            'groupedTickets' => $groupedTickets,
            'groupedPendingTickets' => $groupedPendingTickets,
            'totalTicketCount' => $totalTicketCount,
            'totalPendingCount' => $totalPendingCount,
            'periodLabel' => $periodLabel,
            'start' => $start,
            'end' => $end,
            'generatedAt' => $now,
        ])
            ->setPaper('a4')
            ->stream($filename);
    }

    private function stream(Request $request, User $staff): Response
    {
        $period = $request->string('period')->value();
        $period = in_array($period, ['week', 'last_week', 'month'], true) ? $period : 'week';
        $now = Carbon::now();

        if ($period === 'last_week') {
            $start = $now->copy()->startOfWeek()->subWeek();
            $end = $now->copy()->startOfWeek()->subWeek()->addDays(6)->endOfDay();
        } elseif ($period === 'month') {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfDay();
        } else {
            $start = $now->copy()->startOfWeek();
            $end = $now->isSunday()
                ? $now->copy()->startOfWeek()->addDays(5)->endOfDay()
                : $now->copy()->endOfDay();
        }

        $tickets = Ticket::query()
            ->where('assigned_to', $staff->id)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->orderBy('resolved_at')
            ->get();

        $pendingTickets = Ticket::query()
            ->where('assigned_to', $staff->id)
            ->whereIn('status', ['in_progress', 'pending_review'])
            ->orderBy('created_at')
            ->get();

        $periodLabel = match ($period) {
            'last_week' => 'Last Week',
            'month' => 'This Month',
            default => 'This Week',
        };
        $filename = sprintf(
            'accomplishment-report-%s-%s-%s.pdf',
            Str::slug($staff->name),
            $period,
            $now->format('Y-m-d'),
        );

        return Pdf::loadView('reports.accomplishment', [
            'staff' => $staff,
            'tickets' => $tickets,
            'pendingTickets' => $pendingTickets,
            'periodLabel' => $periodLabel,
            'start' => $start,
            'end' => $end,
            'generatedAt' => $now,
        ])
            ->setPaper('a4')
            ->stream($filename);
    }
}
