<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $deed->reference }}</title>
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
            color: #c2410c;
            font-size: 14px;
            margin-top: 4px;
        }

        .pdf-logo {
            display: block;
            height: 56px;
            margin: 0 auto 6px;
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
            background: #fff7ed;
            color: #9a3412;
            width: 32%;
        }

        .signature-grid {
            margin-top: 70px;
            width: 100%;
        }

        .signature {
            text-align: center;
            width: 50%;
        }

        .line {
            border-top: 1px solid #111827;
            margin: 0 auto 6px;
            width: 78%;
        }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Deed of Sale', 'logoBase64' => $logoBase64 ?? null])

    <div class="meta">
        <div><span class="label">Reference:</span> {{ $deed->reference }}</div>
        <div><span class="label">Generated Date:</span> {{ $deed->generated_at->format('F d, Y') }}</div>
    </div>

    <div class="section">
        <p>
            This Deed of Sale certifies that <span class="label">{{ $document['seller'] }}</span>
            has transferred ownership of the motor vehicle described below to
            <span class="label">{{ $document['buyer'] }}</span>, subject to the recorded sales
            transaction and payment details maintained in the CDO Car Trading system.
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
        <h3>Sales Details</h3>
        <table>
            <tr>
                <th>Sales Reference</th>
                <td>{{ $document['sale']['reference'] ?? 'N/A' }}</td>
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
                <th>Date of Sale</th>
                <td>{{ $document['sale']['sold_at'] ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <table class="signature-grid">
        <tr>
            <td class="signature">
                <div class="line"></div>
                Seller / Authorized Representative
            </td>
            <td class="signature">
                <div class="line"></div>
                Buyer / Customer
            </td>
        </tr>
    </table>
</body>
</html>
