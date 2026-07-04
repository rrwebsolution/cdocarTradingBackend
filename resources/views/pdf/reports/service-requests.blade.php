<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Service Request Report</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.5; }
        h1, h2 { margin: 0; text-align: center; }
        h1 { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        h2 { color: #9a3412; font-size: 13px; margin-top: 4px; }
        .meta { margin-top: 16px; display: flex; justify-content: space-between; font-size: 10px; color: #374151; }
        table { border-collapse: collapse; margin-top: 14px; width: 100%; }
        td, th { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #fff7ed; color: #9a3412; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>Service Request Report</h1>
    <h2>CDO Car Trading</h2>

    <div class="meta">
        <span>Generated: {{ now()->format('F d, Y g:i A') }}</span>
        <span>
            @if ($from || $to || $status)
                {{ $from ? 'From ' . \Illuminate\Support\Carbon::parse($from)->format('M d, Y') : '' }}
                {{ $to ? ' To ' . \Illuminate\Support\Carbon::parse($to)->format('M d, Y') : '' }}
                {{ $status ? ' • Status: ' . $status : '' }}
            @else
                All Records
            @endif
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Reference</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Service Type</th>
                <th>Status</th>
                <th>Preferred Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->reference ?? 'N/A' }}</td>
                    <td>{{ $row->customer->name ?? 'N/A' }}</td>
                    <td>{{ $row->vehicle->name ?? 'N/A' }}</td>
                    <td>{{ $row->service_type ?? 'N/A' }}</td>
                    <td>{{ ucwords((string) $row->status) }}</td>
                    <td>{{ $row->preferred_service_date ? \Illuminate\Support\Carbon::parse($row->preferred_service_date)->format('M d, Y') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="muted" colspan="6">No records match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
