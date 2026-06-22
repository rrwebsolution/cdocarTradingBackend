<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\PreSaleRepair;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PreSaleRepairController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(PreSaleRepair::class, ['vehicle', 'assignedStaff']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('before_photo')) {
            $validated['before_photo_url'] = Storage::url($request->file('before_photo')->store('pre-sale-repairs', 'public'));
        }

        if ($request->hasFile('after_photo')) {
            $validated['after_photo_url'] = Storage::url($request->file('after_photo')->store('pre-sale-repairs', 'public'));
        }

        $repair = PreSaleRepair::create($validated);

        return $this->storedResponse($repair->load(['vehicle', 'assignedStaff']));
    }

    public function show(PreSaleRepair $preSaleRepair): JsonResponse
    {
        return $this->showResponse($preSaleRepair->load(['vehicle', 'assignedStaff']));
    }

    public function update(Request $request, PreSaleRepair $preSaleRepair): JsonResponse
    {
        $validated = $this->validated($request, $preSaleRepair);

        if ($request->hasFile('before_photo')) {
            $validated['before_photo_url'] = Storage::url($request->file('before_photo')->store('pre-sale-repairs', 'public'));
        }

        if ($request->hasFile('after_photo')) {
            $validated['after_photo_url'] = Storage::url($request->file('after_photo')->store('pre-sale-repairs', 'public'));
        }

        $preSaleRepair->update($validated);

        return $this->updatedResponse($preSaleRepair->fresh(['vehicle', 'assignedStaff']));
    }

    public function destroy(PreSaleRepair $preSaleRepair): JsonResponse
    {
        $preSaleRepair->delete();

        return $this->deletedResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?PreSaleRepair $preSaleRepair = null): array
    {
        return $request->validate([
            'action_taken' => ['nullable', 'string'],
            'affected_part' => ['nullable', 'string', 'max:255'],
            'after_photo' => ['nullable', 'image', 'max:10240'],
            'after_photo_url' => ['nullable', 'string', 'max:2048'],
            'assigned_staff_id' => ['nullable', 'exists:staff,id'],
            'before_photo' => ['nullable', 'image', 'max:10240'],
            'before_photo_url' => ['nullable', 'string', 'max:2048'],
            'completed_at' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'inspected_at' => ['nullable', 'date'],
            'issue' => [$preSaleRepair ? 'sometimes' : 'required', 'string'],
            'reference' => [$preSaleRepair ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('pre_sale_repairs', 'reference')->ignore($preSaleRepair?->id)],
            'remarks' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$preSaleRepair ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
