<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobOrderController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(JobOrder::class, ['serviceRequest.customer', 'vehicle', 'assignedStaff']);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storedResponse(JobOrder::create($this->validated($request)));
    }

    public function update(Request $request, JobOrder $jobOrder): JsonResponse
    {
        $jobOrder->update($this->validated($request, true));

        return $this->updatedResponse($jobOrder->fresh(['serviceRequest', 'vehicle', 'assignedStaff']));
    }

    public function destroy(JobOrder $jobOrder): JsonResponse
    {
        $jobOrder->delete();

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'activity' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'assigned_staff_id' => ['nullable', 'exists:staff,id'],
            'maintenance_record' => ['nullable', 'string'],
            'reference' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'repair_status' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date'],
            'service_request_id' => ['nullable', 'exists:service_requests,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$updating ? 'sometimes' : 'required', 'exists:vehicles,id'],
            'washing_status' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
