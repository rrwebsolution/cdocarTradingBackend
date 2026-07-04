<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Customer Report</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.5; }
        h1, h2 { margin: 0; text-align: center; }
        h1 { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        h2 { color: #0e7490; font-size: 13px; margin-top: 4px; }
        .meta { margin-top: 16px; display: flex; justify-content: space-between; font-size: 10px; color: #374151; }
        table { border-collapse: collapse; margin-top: 14px; width: 100%; }
        td, th { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #ecfeff; color: #155e75; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Customer Report</h1>
    <h2>CDO Car Trading</h2>

    <div class="meta">
        <span>Generated: {{ now()->format('F d, Y g:i A') }}</span>
        <span>
            @if ($from || $to)
                {{ $from ? 'From ' . \Illuminate\Support\Carbon::parse($from)->format('M d, Y') : '' }}
                {{ $to ? ' To ' . \Illuminate\Support\Carbon::parse($to)->format('M d, Y') : '' }}
            @else
                All Records
            @endif
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Purchases</th>
                <th>Reservations</th>
                <th>Status</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->name ?? 'N/A' }}</td>
                    <td>{{ $row->email ?? 'N/A' }}</td>
                    <td>{{ $row->contact ?? 'N/A' }}</td>
                    <td>{{ $row->sales_transactions_count }}</td>
                    <td>{{ $row->reservations_count }}</td>
                    <td>{{ ucwords((string) $row->status) }}</td>
                    <td>{{ $row->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="muted" colspan="7">No records match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
