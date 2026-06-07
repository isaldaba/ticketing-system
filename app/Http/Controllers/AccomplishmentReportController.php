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
        return $this->download($request, $request->user());
    }

    public function admin(Request $request, User $user): Response
    {
        abort_unless($user->role === 'staff', 404);

        return $this->download($request, $user);
    }

    private function download(Request $request, User $staff): Response
    {
        $period = $request->string('period')->value();
        $period = in_array($period, ['week', 'month'], true) ? $period : 'week';
        $now = Carbon::now();
        $start = $period === 'month'
            ? $now->copy()->startOfMonth()
            : $now->copy()->startOfWeek();
        $end = $now->copy()->endOfDay();

        $tickets = Ticket::query()
            ->where('assigned_to', $staff->id)
            ->where('status', 'resolved')
            ->whereBetween('resolved_at', [$start, $end])
            ->orderBy('resolved_at')
            ->get();

        $periodLabel = $period === 'month' ? 'This Month' : 'This Week';
        $filename = sprintf(
            'accomplishment-report-%s-%s-%s.pdf',
            Str::slug($staff->name),
            $period,
            $now->format('Y-m-d'),
        );

        return Pdf::loadView('reports.accomplishment', [
            'staff' => $staff,
            'tickets' => $tickets,
            'periodLabel' => $periodLabel,
            'start' => $start,
            'end' => $end,
            'generatedAt' => $now,
        ])
            ->setPaper('a4')
            ->download($filename);
    }
}
