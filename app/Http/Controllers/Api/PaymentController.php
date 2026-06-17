<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(Payment::class, ['customer', 'salesTransaction.vehicle']);
    }

    public function store(Request $request): JsonResponse
    {
        $payment = Payment::create($this->validated($request));
        $this->syncSaleTotals($payment);

        return $this->storedResponse($payment->load(['customer', 'salesTransaction']));
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        $payment->update($this->validated($request, true));
        $this->syncSaleTotals($payment);

        return $this->updatedResponse($payment->fresh(['customer', 'salesTransaction']));
    }

    public function destroy(Payment $payment): JsonResponse
    {
        $sale = $payment->salesTransaction;
        $payment->delete();

        if ($sale) {
            $paid = $sale->payments()->sum('amount');
            $sale->update([
                'paid_amount' => $paid,
                'balance' => $sale->total_amount - $paid,
            ]);
        }

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'amount' => [$updating ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'customer_id' => [$updating ? 'sometimes' : 'required', 'exists:customers,id'],
            'method' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'proof_url' => ['nullable', 'string', 'max:2048'],
            'receipt_number' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'sales_transaction_id' => [$updating ? 'sometimes' : 'required', 'exists:sales_transactions,id'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function syncSaleTotals(Payment $payment): void
    {
        $sale = $payment->salesTransaction;

        if (! $sale) {
            return;
        }

        $paid = $sale->payments()->sum('amount');
        $sale->update([
            'paid_amount' => $paid,
            'balance' => $sale->total_amount - $paid,
            'status' => ($sale->total_amount - $paid) <= 0 ? 'paid' : 'partial',
        ]);
    }
}
