<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->reference }}</title>
    <style>
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.55;
        }

        h1, h2 {
            margin: 0;
            text-align: center;
        }

        h1 {
            font-size: 22px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        h2 {
            color: #0f766e;
            font-size: 14px;
            margin-top: 4px;
        }

        .meta {
            margin-top: 24px;
            text-align: right;
        }

        .section {
            margin-top: 22px;
        }

        .label {
            font-weight: 700;
        }

        table {
            border-collapse: collapse;
            margin-top: 10px;
            width: 100%;
        }

        td, th {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f0fdfa;
            color: #115e59;
            width: 32%;
        }

        .totals td, .totals th {
            font-weight: 700;
        }
    </style>
</head>
<body>
    <h1>Sales Invoice</h1>
    <h2>CDO Car Trading</h2>

    <div class="meta">
        <div><span class="label">Invoice No.:</span> {{ $invoice->reference }}</div>
        <div><span class="label">Date Issued:</span> {{ $invoice->generated_at->format('F d, Y') }}</div>
    </div>

    <div class="section">
        <p>
            Billed to <span class="label">{{ $document['buyer'] }}</span> by
            <span class="label">{{ $document['seller'] }}</span> for the purchase of the vehicle
            described below, under sales reference <span class="label">{{ $document['sale']['reference'] ?? 'N/A' }}</span>.
        </p>
    </div>

    <div class="section">
        <h3>Vehicle Details</h3>
        <table>
            @foreach (($document['vehicle'] ?? []) as $label => $value)
                <tr>
                    <th>{{ ucwords(str_replace('_', ' ', $label)) }}</th>
                    <td>{{ $value ?: 'N/A' }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Payment Summary</h3>
        <table class="totals">
            <tr>
                <th>Payment Method</th>
                <td>{{ ucwords((string) ($document['sale']['payment_method'] ?? 'N/A')) }}</td>
            </tr>
            <tr>
                <th>Total Amount</th>
                <td>PHP {{ number_format((float) ($document['sale']['total_amount'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th>Paid Amount</th>
                <td>PHP {{ number_format((float) ($document['sale']['paid_amount'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th>Balance</th>
                <td>PHP {{ number_format((float) ($document['sale']['balance'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th>Date of Sale</th>
                <td>{{ $document['sale']['sold_at'] ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
