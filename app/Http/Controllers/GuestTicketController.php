<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuestTicketController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'requester_name' => ['required', 'string', 'max:120'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'concern' => ['required', 'string', 'max:5000'],
        ]);

        Ticket::create([
            ...$validated,
            'requester_email' => null,
            'due_date' => null,
            'created_by' => null,
            'status' => 'guest_review',
            'admin_review_seen_at' => null,
        ]);

        return back()->with('success', 'Your ticket request was sent to admin for review.');
    }
}
