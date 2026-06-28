<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(ServiceRequest::class, ['customer', 'vehicle', 'jobOrder']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = Storage::url($request->file('photo')->store('service-requests', 'public'));
        }

        return $this->storedResponse(ServiceRequest::create($validated));
    }

    public function update(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $validated = $this->validated($request, true);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = Storage::url($request->file('photo')->store('service-requests', 'public'));
        }

        $serviceRequest->update($validated);

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
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'preferred_service_date' => ['nullable', 'date'],
            'progress' => ['nullable', 'string', 'max:255'],
            'reference' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'service_type' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$updating ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
