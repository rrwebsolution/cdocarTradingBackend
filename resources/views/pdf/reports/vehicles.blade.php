<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Vehicle Inventory Report</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.5; }
        h1, h2 { margin: 0; text-align: center; }
        h1 { font-size: 20px; letter-spacing: 1px; text-transform: uppercase; }
        h2 { color: #b45309; font-size: 13px; margin-top: 4px; }
        .pdf-logo { display: block; height: 48px; margin: 0 auto 6px; }
        .meta { margin-top: 16px; display: flex; justify-content: space-between; font-size: 10px; color: #374151; }
        table { border-collapse: collapse; margin-top: 14px; width: 100%; }
        td, th { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #fffbeb; color: #92400e; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Vehicle Inventory Report', 'logoBase64' => $logoBase64 ?? null])

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
                <th>Stock No.</th>
                <th>Name</th>
                <th>Brand / Model / Year</th>
                <th>Color</th>
                <th>Selling Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->stock_no ?? 'N/A' }}</td>
                    <td>{{ $row->name ?? 'N/A' }}</td>
                    <td>{{ trim(($row->brand ?? '') . ' ' . ($row->model ?? '') . ' ' . ($row->year ?? '')) ?: 'N/A' }}</td>
                    <td>{{ $row->color ?? 'N/A' }}</td>
                    <td>PHP {{ number_format((float) $row->selling_price, 2) }}</td>
                    <td>{{ ucwords((string) $row->status) }}</td>
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
