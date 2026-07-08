<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesTransaction;
use App\Support\PdfLogo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SalesInvoiceController extends Controller
{
    public function show(SalesTransaction $salesTransaction): JsonResponse
    {
        $invoice = $this->findOrCreateInvoice($salesTransaction);

        return response()->json(['data' => $invoice]);
    }

    public function pdf(SalesTransaction $salesTransaction): Response
    {
        $invoice = $this->findOrCreateInvoice($salesTransaction);
        $pdf = Pdf::loadView('pdf.sales-invoice', [
            'invoice' => $invoice,
            'document' => $invoice->document_data,
            'logoBase64' => PdfLogo::base64(),
        ])->setPaper('legal');

        return $pdf->stream($invoice->reference.'.pdf');
    }

    private function findOrCreateInvoice(SalesTransaction $salesTransaction): SalesInvoice
    {
        $salesTransaction->load(['customer', 'vehicle']);

        return SalesInvoice::firstOrCreate(
            ['sales_transaction_id' => $salesTransaction->id],
            [
                'reference' => 'INV-'.$salesTransaction->reference,
                'generated_at' => Carbon::today(),
                'document_data' => [
                    'seller' => 'CDO Car Trading',
                    'buyer' => $salesTransaction->customer?->name,
                    'vehicle' => $salesTransaction->vehicle?->only([
                        'name',
                        'brand',
                        'model',
                        'year',
                        'color',
                        'plate_number',
                    ]),
                    'sale' => [
                        'reference' => $salesTransaction->reference,
                        'payment_method' => $salesTransaction->payment_method,
                        'total_amount' => $salesTransaction->total_amount,
                        'paid_amount' => $salesTransaction->paid_amount,
                        'balance' => $salesTransaction->balance,
                        'sold_at' => $salesTransaction->sold_at?->toDateString(),
                    ],
                ],
            ]
        )->fresh();
    }
}
