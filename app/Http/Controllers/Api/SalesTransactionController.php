<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\SalesTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesTransactionController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(SalesTransaction::class, ['customer', 'vehicle', 'payments', 'deedOfSale']);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['balance'] = ($data['total_amount'] ?? 0) - ($data['paid_amount'] ?? 0);
        $transaction = SalesTransaction::create($data);

        if (($transaction->balance <= 0) && in_array($transaction->status, ['paid', 'completed'], true)) {
            $transaction->vehicle()->update(['status' => 'sold']);
        }

        return $this->storedResponse($transaction->load(['customer', 'vehicle']));
    }

    public function update(Request $request, SalesTransaction $salesTransaction): JsonResponse
    {
        $data = $this->validated($request, true);

        if (array_key_exists('total_amount', $data) || array_key_exists('paid_amount', $data)) {
            $total = $data['total_amount'] ?? $salesTransaction->total_amount;
            $paid = $data['paid_amount'] ?? $salesTransaction->paid_amount;
            $data['balance'] = $total - $paid;
        }

        $salesTransaction->update($data);

        if (($salesTransaction->balance <= 0) && in_array($salesTransaction->status, ['paid', 'completed'], true)) {
            $salesTransaction->vehicle()->update(['status' => 'sold']);
        }

        return $this->updatedResponse($salesTransaction->fresh(['customer', 'vehicle', 'payments']));
    }

    public function destroy(SalesTransaction $salesTransaction): JsonResponse
    {
        $salesTransaction->delete();

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'customer_id' => [$updating ? 'sometimes' : 'required', 'exists:customers,id'],
            'financing_details' => ['nullable', 'array'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'reservation_id' => ['nullable', 'exists:reservations,id'],
            'sold_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'vehicle_id' => [$updating ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
