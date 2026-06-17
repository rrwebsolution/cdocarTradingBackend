<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeedOfSale;
use App\Models\SalesTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class DeedOfSaleController extends Controller
{
    public function show(SalesTransaction $salesTransaction): JsonResponse
    {
        $deed = $this->findOrCreateDeed($salesTransaction);

        return response()->json(['data' => $deed]);
    }

    public function pdf(SalesTransaction $salesTransaction): Response
    {
        $deed = $this->findOrCreateDeed($salesTransaction);
        $pdf = Pdf::loadView('pdf.deed-of-sale', [
            'deed' => $deed,
            'document' => $deed->document_data,
        ])->setPaper('legal');

        return $pdf->stream($deed->reference.'.pdf');
    }

    private function findOrCreateDeed(SalesTransaction $salesTransaction): DeedOfSale
    {
        $salesTransaction->load(['customer', 'vehicle']);

        return DeedOfSale::firstOrCreate(
            ['sales_transaction_id' => $salesTransaction->id],
            [
                'reference' => 'DOS-'.$salesTransaction->reference,
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
                        'engine_number',
                        'chassis_number',
                        'plate_number',
                    ]),
                    'sale' => [
                        'reference' => $salesTransaction->reference,
                        'total_amount' => $salesTransaction->total_amount,
                        'paid_amount' => $salesTransaction->paid_amount,
                        'sold_at' => $salesTransaction->sold_at?->toDateString(),
                    ],
                ],
            ]
        )->fresh();
    }
}
