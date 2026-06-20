<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accomplishment Report</title>
    <style>
        @page { margin: 54px 56px; }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.55;
        }

        h1 {
            margin: 0 0 14px;
            font-size: 22px;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            margin-bottom: 26px;
            border-collapse: collapse;
        }

        .meta td {
            padding: 6px 0;
            vertical-align: top;
        }

        .meta .label {
            width: 94px;
            color: #6b7280;
            font-weight: bold;
        }

        .summary {
            margin-bottom: 24px;
            color: #374151;
        }

        h2 {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: bold;
        }

        .staff-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .staff-section:not(:first-of-type) {
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .staff-name {
            margin: 0 0 10px;
            font-size: 13px;
            font-weight: bold;
            color: #4f46e5;
        }

        .item {
            page-break-inside: avoid;
            margin-bottom: 18px;
        }

        .title {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .resolution {
            margin-left: 18px;
            color: #374151;
            white-space: pre-line;
        }

        .date {
            margin: 5px 0 0 18px;
            color: #6b7280;
            font-size: 9px;
        }

        .empty {
            color: #6b7280;
        }

        .generated {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <h1>Accomplishment Report</h1>

    @if (isset($staffMembers))
        <table class="meta">
            <tr>
                <td class="label">Staff:</td>
                <td>{{ $staffMembers->pluck('name')->join(', ') }}</td>
            </tr>
            <tr>
                <td class="label">Period:</td>
                <td>{{ $start->format('M j, Y') }} - {{ $end->format('M j, Y') }} ({{ $periodLabel }})</td>
            </tr>
            <tr>
                <td class="label">Resolved:</td>
                <td>{{ $totalTicketCount }} ticket{{ $totalTicketCount === 1 ? '' : 's' }}</td>
            </tr>
        </table>

        <p class="summary">
            {{ $staffMembers->count() }} staff member{{ $staffMembers->count() === 1 ? '' : 's' }} resolved {{ $totalTicketCount }} ticket{{ $totalTicketCount === 1 ? '' : 's' }} during this period.
        </p>

        @foreach ($staffMembers as $member)
            @php
                $tickets = $groupedTickets[$member->id] ?? collect();
            @endphp
            <div class="staff-section">
                <h3 class="staff-name">{{ $member->name }}</h3>

                <h2>Concerns</h2>

                @forelse ($tickets as $index => $ticket)
                    <div class="item">
                        <div class="title">{{ $index + 1 }}. {{ $ticket->title }}</div>
                        <div class="resolution">{{ $ticket->resolution_note ?: 'Ticket resolved and approved by admin.' }}</div>
                        <div class="date">Resolved {{ $ticket->resolved_at?->format('M j, Y') }}</div>
                    </div>
                @empty
                    <p class="empty">No tickets were resolved during this period.</p>
                @endforelse

                @php
                    $memberPending = $groupedPendingTickets[$member->id] ?? collect();
                @endphp

                <h2>Pending Tickets</h2>

                @forelse ($memberPending as $index => $ticket)
                    <div class="item">
                        <div class="title">{{ $index + 1 }}. {{ $ticket->title }}</div>
                        <div class="resolution">{{ $ticket->concern }}</div>
                        <div class="date">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }} since {{ $ticket->created_at?->format('M j, Y') }}</div>
                    </div>
                @empty
                    <p class="empty">No pending tickets.</p>
                @endforelse
            </div>
        @endforeach
    @else
        <table class="meta">
            <tr>
                <td class="label">Staff:</td>
                <td>{{ $staff->name }}</td>
            </tr>
            <tr>
                <td class="label">Period:</td>
                <td>{{ $start->format('M j, Y') }} - {{ $end->format('M j, Y') }} ({{ $periodLabel }})</td>
            </tr>
            <tr>
                <td class="label">Resolved:</td>
                <td>{{ $tickets->count() }} ticket{{ $tickets->count() === 1 ? '' : 's' }}</td>
            </tr>
        </table>

        <p class="summary">
            {{ $staff->name }} resolved {{ $tickets->count() }} ticket{{ $tickets->count() === 1 ? '' : 's' }} during this period.
        </p>

        <h2>Concerns</h2>

        @forelse ($tickets as $index => $ticket)
            <div class="item">
                <div class="title">{{ $index + 1 }}. {{ $ticket->title }}</div>
                <div class="resolution">{{ $ticket->resolution_note ?: 'Ticket resolved and approved by admin.' }}</div>
                <div class="date">Resolved {{ $ticket->resolved_at?->format('M j, Y') }}</div>
            </div>
        @empty
            <p class="empty">No tickets were resolved during this period.</p>
        @endforelse

        <h2>Pending Tickets</h2>

        @forelse ($pendingTickets as $index => $ticket)
            <div class="item">
                <div class="title">{{ $index + 1 }}. {{ $ticket->title }}</div>
                <div class="resolution">{{ $ticket->concern }}</div>
                <div class="date">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }} since {{ $ticket->created_at?->format('M j, Y') }}</div>
            </div>
        @empty
            <p class="empty">No pending tickets.</p>
        @endforelse
    @endif

    <div class="generated">
        Generated on {{ $generatedAt->format('M j, Y g:i A') }}
    </div>
</body>
</html>
