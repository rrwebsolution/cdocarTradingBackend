<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\FinancingRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinancingRecordController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(FinancingRecord::class, ['customer', 'vehicle', 'salesTransaction']);
    }

    public function store(Request $request): JsonResponse
    {
        $record = FinancingRecord::create($this->validated($request));

        return $this->storedResponse($record->load(['customer', 'vehicle', 'salesTransaction']));
    }

    public function show(FinancingRecord $financingRecord): JsonResponse
    {
        return $this->showResponse($financingRecord->load(['customer', 'vehicle', 'salesTransaction']));
    }

    public function update(Request $request, FinancingRecord $financingRecord): JsonResponse
    {
        $financingRecord->update($this->validated($request, $financingRecord));

        return $this->updatedResponse($financingRecord->fresh(['customer', 'vehicle', 'salesTransaction']));
    }

    public function destroy(FinancingRecord $financingRecord): JsonResponse
    {
        $financingRecord->delete();

        return $this->deletedResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?FinancingRecord $financingRecord = null): array
    {
        return $request->validate([
            'application_number' => ['nullable', 'string', 'max:255'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'approved_at' => ['nullable', 'date'],
            'customer_id' => [$financingRecord ? 'sometimes' : 'required', 'exists:customers,id'],
            'documents' => ['nullable', 'array'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'financing_company' => [$financingRecord ? 'sometimes' : 'required', 'string', 'max:255'],
            'reference' => [$financingRecord ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('financing_records', 'reference')->ignore($financingRecord?->id)],
            'remarks' => ['nullable', 'string'],
            'sales_transaction_id' => ['nullable', 'exists:sales_transactions,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$financingRecord ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
