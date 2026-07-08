<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\FinancingRecord;
use App\Models\Reservation;
use App\Models\Vehicle;
use App\Rules\VehicleIsAvailable;
use App\Support\CustomerLocationType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FinancingRecordController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(FinancingRecord::class, ['customer', 'vehicle', 'salesTransaction']);
    }

    /**
     * Financing requirements lock the vehicle immediately on submission
     * (no manual review gate) by creating a linked reservation in the same
     * transaction as the financing record.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);
        $validated['requirements_submitted_at'] ??= now()->toDateString();

        $record = DB::transaction(function () use ($validated) {
            $vehicle = Vehicle::whereKey($validated['vehicle_id'])->lockForUpdate()->firstOrFail();

            $record = FinancingRecord::create($validated);

            Reservation::create([
                'reference' => 'RES-'.$record->reference,
                'customer_id' => $record->customer_id,
                'vehicle_id' => $record->vehicle_id,
                'financing_record_id' => $record->id,
                'amount' => $record->down_payment,
                'status' => 'reserved',
                'reserved_at' => now()->toDateString(),
            ]);

            $vehicle->update(['status' => 'reserved']);

            return $record;
        });

        return $this->storedResponse($record->load(['customer', 'vehicle', 'salesTransaction', 'reservation']));
    }

    public function show(FinancingRecord $financingRecord): JsonResponse
    {
        return $this->showResponse($financingRecord->load(['customer', 'vehicle', 'salesTransaction', 'reservation']));
    }

    public function update(Request $request, FinancingRecord $financingRecord): JsonResponse
    {
        $financingRecord->update($this->validated($request, $financingRecord));

        return $this->updatedResponse($financingRecord->fresh(['customer', 'vehicle', 'salesTransaction', 'reservation']));
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
            'customer_location_type' => ['nullable', Rule::in(CustomerLocationType::ALL)],
            'documents' => ['nullable', 'array'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'financing_company' => [$financingRecord ? 'sometimes' : 'required', 'string', 'max:255'],
            'reference' => [$financingRecord ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('financing_records', 'reference')->ignore($financingRecord?->id)],
            'remarks' => ['nullable', 'string'],
            'requirements_submitted_at' => ['nullable', 'date'],
            'sales_transaction_id' => ['nullable', 'exists:sales_transactions,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => array_filter([
                $financingRecord ? 'sometimes' : 'required',
                'exists:vehicles,id',
                $financingRecord ? null : new VehicleIsAvailable,
            ]),
        ]);
    }
}
