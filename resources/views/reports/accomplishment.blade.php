<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accomplishment Report</title>
    <style>
        @page { size: A4; margin: 50px 56px; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #1f2937;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.6;
            background: #fff;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 3px solid #4f46e5;
        }

        .header h1 {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            margin: 0;
            font-size: 11px;
            color: #6b7280;
        }

        .meta-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 20px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 5px 0;
            vertical-align: top;
        }

        .meta .label {
            width: 90px;
            color: #6b7280;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .meta .value {
            color: #111827;
            font-weight: 500;
        }

        .summary {
            background: #eef2ff;
            border-left: 4px solid #4f46e5;
            padding: 12px 16px;
            margin: 0 0 24px;
            color: #3730a3;
            font-size: 11px;
            border-radius: 0 6px 6px 0;
        }

        .section-title {
            margin: 20px 0 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e5e7eb;
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .section-title.accomplishments {
            color: #059669;
            border-bottom-color: #a7f3d0;
        }

        .section-title.pending {
            color: #d97706;
            border-bottom-color: #fde68a;
        }

        .staff-section {
            margin-bottom: 28px;
            page-break-inside: avoid;
        }

        .staff-section:not(:first-of-type) {
            padding-top: 18px;
            border-top: 1px dashed #d1d5db;
        }

        .staff-name {
            margin: 0 0 12px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            background: #4f46e5;
            border-radius: 4px;
            display: inline-block;
        }

        .item {
            page-break-inside: avoid;
            margin-bottom: 12px;
            padding: 10px 14px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            border-left: 3px solid #d1d5db;
        }

        .item.resolved {
            border-left-color: #34d399;
        }

        .item.pending {
            border-left-color: #fbbf24;
        }

        .item-number {
            display: inline-block;
            width: 20px;
            height: 20px;
            line-height: 20px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            color: #fff;
            background: #9ca3af;
            border-radius: 50%;
            margin-right: 6px;
        }

        .item.resolved .item-number {
            background: #059669;
        }

        .item.pending .item-number {
            background: #d97706;
        }

        .title {
            display: inline;
            font-weight: 600;
            color: #111827;
        }

        .resolution {
            margin-top: 6px;
            padding: 8px 12px;
            color: #4b5563;
            white-space: pre-line;
            background: #f9fafb;
            border-radius: 4px;
            font-size: 10.5px;
            line-height: 1.5;
        }

        .empty {
            color: #9ca3af;
            font-style: italic;
            padding: 8px 0;
        }

        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-card {
            display: table-cell;
            width: 33.33%;
            padding: 12px 16px;
            text-align: center;
            vertical-align: middle;
        }

        .stat-card .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .stat-card .stat-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stat-card.resolved .stat-number { color: #059669; }
        .stat-card.pending .stat-number { color: #d97706; }
        .stat-card.total .stat-number { color: #4f46e5; }

        .generated {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 2px solid #e5e7eb;
            color: #9ca3af;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Accomplishment Report</h1>
        <p class="subtitle">RMMCI Staff Performance Summary</p>
    </div>

    @if (isset($staffMembers))
        <div class="stats">
            <div class="stat-card total">
                <div class="stat-number">{{ $staffMembers->count() }}</div>
                <div class="stat-label">Staff Member{{ $staffMembers->count() === 1 ? '' : 's' }}</div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-number">{{ $totalTicketCount }}</div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number">{{ $totalPendingCount }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="meta-box">
            <table class="meta">
                <tr>
                    <td class="label">Staff</td>
                    <td class="value">{{ $staffMembers->pluck('name')->join(', ') }}</td>
                </tr>
                <tr>
                    <td class="label">Period</td>
                    <td class="value">{{ $start->format('M j, Y') }} - {{ $end->format('M j, Y') }} ({{ $periodLabel }})</td>
                </tr>
            </table>
        </div>

        <div class="summary">
            {{ $staffMembers->count() }} staff member{{ $staffMembers->count() === 1 ? '' : 's' }} resolved {{ $totalTicketCount }} ticket{{ $totalTicketCount === 1 ? '' : 's' }} during this period.
        </div>

        @foreach ($staffMembers as $member)
            @php
                $resolvedTickets = $groupedTickets[$member->id] ?? collect();
                $pendingTicketsForStaff = $groupedPendingTickets[$member->id] ?? collect();
            @endphp
            <div class="staff-section">
                <div class="staff-name">{{ $member->name }}</div>

                <div class="section-title accomplishments">RMMCI Accomplishments</div>

                @forelse ($resolvedTickets as $index => $ticket)
                    <div class="item resolved">
                        <span class="item-number">{{ $index + 1 }}</span>
                        <span class="title">{{ $ticket->title }}</span>
                        <div class="resolution">{{ $ticket->resolution_note ?: 'Ticket resolved and approved by admin.' }}</div>
                    </div>
                @empty
                    <p class="empty">No tickets resolved during this period.</p>
                @endforelse

                <div class="section-title pending">Pending / In Progress</div>

                @forelse ($pendingTicketsForStaff as $index => $ticket)
                    <div class="item pending">
                        <span class="item-number">{{ $index + 1 }}</span>
                        <span class="title">{{ $ticket->title }}</span>
                        <div class="resolution">{{ $ticket->concern }}</div>
                    </div>
                @empty
                    <p class="empty">No pending tickets.</p>
                @endforelse
            </div>
        @endforeach
    @else
        <div class="stats">
            <div class="stat-card total">
                <div class="stat-number">1</div>
                <div class="stat-label">Staff Member</div>
            </div>
            <div class="stat-card resolved">
                <div class="stat-number">{{ $tickets->count() }}</div>
                <div class="stat-label">Resolved</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number">{{ $pendingTickets->count() }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="meta-box">
            <table class="meta">
                <tr>
                    <td class="label">Staff</td>
                    <td class="value">{{ $staff->name }}</td>
                </tr>
                <tr>
                    <td class="label">Period</td>
                    <td class="value">{{ $start->format('M j, Y') }} - {{ $end->format('M j, Y') }} ({{ $periodLabel }})</td>
                </tr>
            </table>
        </div>

        <div class="summary">
            {{ $staff->name }} resolved {{ $tickets->count() }} ticket{{ $tickets->count() === 1 ? '' : 's' }} during this period.
        </div>

        <div class="section-title accomplishments">RMMCI Accomplishments</div>

        @forelse ($tickets as $index => $ticket)
            <div class="item resolved">
                <span class="item-number">{{ $index + 1 }}</span>
                <span class="title">{{ $ticket->title }}</span>
                <div class="resolution">{{ $ticket->resolution_note ?: 'Ticket resolved and approved by admin.' }}</div>
            </div>
        @empty
            <p class="empty">No tickets resolved during this period.</p>
        @endforelse

        <div class="section-title pending">Pending / In Progress</div>

        @forelse ($pendingTickets as $index => $ticket)
            <div class="item pending">
                <span class="item-number">{{ $index + 1 }}</span>
                <span class="title">{{ $ticket->title }}</span>
                <div class="resolution">{{ $ticket->concern }}</div>
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
