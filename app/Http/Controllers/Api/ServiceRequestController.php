<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(ServiceRequest::class, ['customer', 'vehicle', 'jobOrder']);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storedResponse(ServiceRequest::create($this->validated($request)));
    }

    public function update(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->update($this->validated($request, true));

        return $this->updatedResponse($serviceRequest->fresh(['customer', 'vehicle', 'jobOrder']));
    }

    public function destroy(ServiceRequest $serviceRequest): JsonResponse
    {
        $serviceRequest->delete();

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'customer_id' => [$updating ? 'sometimes' : 'required', 'exists:customers,id'],
            'issue' => ['nullable', 'string'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'progress' => ['nullable', 'string', 'max:255'],
            'reference' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'service_type' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$updating ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
