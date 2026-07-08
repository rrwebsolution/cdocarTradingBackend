<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.5; }
        h1, h2 { margin: 0; text-align: center; }
        h1 { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        h2 { color: #0f766e; font-size: 13px; margin-top: 4px; }
        .pdf-logo { display: block; height: 48px; margin: 0 auto 6px; }
        .meta { margin-top: 16px; display: flex; justify-content: space-between; font-size: 10px; color: #374151; }
        table { border-collapse: collapse; margin-top: 14px; width: 100%; }
        td, th { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f0fdfa; color: #115e59; }
        tfoot td { font-weight: 700; background: #f9fafb; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Sales Report', 'logoBase64' => $logoBase64 ?? null])

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
                <th>Sales No.</th>
                <th>Customer</th>
                <th>Vehicle</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->reference ?? 'N/A' }}</td>
                    <td>{{ $row->customer->name ?? 'N/A' }}</td>
                    <td>{{ $row->vehicle->name ?? 'N/A' }}</td>
                    <td>PHP {{ number_format((float) $row->total_amount, 2) }}</td>
                    <td>{{ ucwords((string) $row->payment_method) }}</td>
                    <td>{{ ucwords((string) $row->status) }}</td>
                    <td>{{ $row->sold_at?->format('M d, Y') ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="muted" colspan="7">No records match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="3">Total</td>
                    <td>PHP {{ number_format((float) $rows->sum('total_amount'), 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
