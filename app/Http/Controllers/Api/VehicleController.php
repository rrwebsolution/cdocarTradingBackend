<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(Vehicle::class);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = Storage::url($request->file('photo')->store('vehicles', 'public'));
        }

        $vehicle = Vehicle::create($validated);

        return $this->storedResponse($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $this->validated($request, $vehicle);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = Storage::url($request->file('photo')->store('vehicles', 'public'));
        }

        $vehicle->update($validated);

        return $this->updatedResponse($vehicle->fresh());
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return $this->deletedResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Vehicle $vehicle = null): array
    {
        $id = $vehicle?->id;

        return $request->validate([
            'brand' => ['nullable', 'string', 'max:255'],
            'chassis_number' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'chassis_number')->ignore($id)],
            'color' => ['nullable', 'string', 'max:255'],
            'condition' => ['nullable', 'string', 'max:255'],
            'engine_number' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'engine_number')->ignore($id)],
            'location' => ['nullable', 'string', 'max:255'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'model' => ['nullable', 'string', 'max:255'],
            'name' => [$vehicle ? 'sometimes' : 'required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'plate_number' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'plate_number')->ignore($id)],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
        ]);
    }
}
