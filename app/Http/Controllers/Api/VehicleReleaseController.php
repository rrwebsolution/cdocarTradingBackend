<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\VehicleRelease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleReleaseController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(VehicleRelease::class, ['customer', 'vehicle', 'salesTransaction']);
    }

    public function store(Request $request): JsonResponse
    {
        $release = VehicleRelease::create($this->validated($request));

        return $this->storedResponse($release->load(['customer', 'vehicle', 'salesTransaction']));
    }

    public function show(VehicleRelease $vehicleRelease): JsonResponse
    {
        return $this->showResponse($vehicleRelease->load(['customer', 'vehicle', 'salesTransaction']));
    }

    public function update(Request $request, VehicleRelease $vehicleRelease): JsonResponse
    {
        $vehicleRelease->update($this->validated($request, $vehicleRelease));

        return $this->updatedResponse($vehicleRelease->fresh(['customer', 'vehicle', 'salesTransaction']));
    }

    public function destroy(VehicleRelease $vehicleRelease): JsonResponse
    {
        $vehicleRelease->delete();

        return $this->deletedResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?VehicleRelease $vehicleRelease = null): array
    {
        return $request->validate([
            'checklist' => ['nullable', 'array'],
            'checklist_status' => ['nullable', 'string', 'max:255'],
            'customer_id' => [$vehicleRelease ? 'sometimes' : 'required', 'exists:customers,id'],
            'document_status' => ['nullable', 'string', 'max:255'],
            'reference' => [$vehicleRelease ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('vehicle_releases', 'reference')->ignore($vehicleRelease?->id)],
            'released_at' => ['nullable', 'date'],
            'released_by' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'sales_transaction_id' => ['nullable', 'exists:sales_transactions,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$vehicleRelease ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
