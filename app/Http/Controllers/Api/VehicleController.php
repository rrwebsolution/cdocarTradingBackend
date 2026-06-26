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

        if ($request->hasFile('main_photo')) {
            $validated['photo_url'] = Storage::url($request->file('main_photo')->store('vehicles', 'public'));
        }

        if ($request->hasFile('interior_photos')) {
            $validated['interior_photo_urls'] = $this->storePhotoCollection($request->file('interior_photos'));
        }

        if ($request->hasFile('exterior_photos')) {
            $validated['exterior_photo_urls'] = $this->storePhotoCollection($request->file('exterior_photos'));
        }

        unset($validated['main_photo'], $validated['photo'], $validated['interior_photos'], $validated['exterior_photos']);

        $vehicle = Vehicle::create($validated);

        return $this->storedResponse($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $this->validated($request, $vehicle);

        if ($request->hasFile('photo')) {
            $validated['photo_url'] = Storage::url($request->file('photo')->store('vehicles', 'public'));
        }

        if ($request->hasFile('main_photo')) {
            $validated['photo_url'] = Storage::url($request->file('main_photo')->store('vehicles', 'public'));
        }

        if ($request->hasFile('interior_photos')) {
            $validated['interior_photo_urls'] = $this->storePhotoCollection($request->file('interior_photos'));
        }

        if ($request->hasFile('exterior_photos')) {
            $validated['exterior_photo_urls'] = $this->storePhotoCollection($request->file('exterior_photos'));
        }

        unset($validated['main_photo'], $validated['photo'], $validated['interior_photos'], $validated['exterior_photos']);

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
            'description' => ['nullable', 'string'],
            'engine_number' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'engine_number')->ignore($id)],
            'exterior_photos' => ['nullable', 'array'],
            'exterior_photos.*' => ['image', 'max:10240'],
            'exterior_photo_urls' => ['nullable', 'array'],
            'features' => ['nullable', 'string'],
            'fuel_type' => ['nullable', 'string', 'max:255'],
            'insurance' => ['nullable', 'string', 'max:255'],
            'interior_photos' => ['nullable', 'array'],
            'interior_photos.*' => ['image', 'max:10240'],
            'interior_photo_urls' => ['nullable', 'array'],
            'location' => ['nullable', 'string', 'max:255'],
            'main_photo' => ['nullable', 'image', 'max:10240'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'model' => ['nullable', 'string', 'max:255'],
            'name' => [$vehicle ? 'sometimes' : 'required', 'string', 'max:255'],
            'or_cr_number' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:10240'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'plate_number' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'plate_number')->ignore($id)],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'registration_expiry' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
            'reservation_fee' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:255'],
            'stock_no' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'stock_no')->ignore($id)],
            'transmission' => ['nullable', 'string', 'max:255'],
            'variant' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
        ]);
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>|\Illuminate\Http\UploadedFile  $files
     * @return array<int, string>
     */
    private function storePhotoCollection(array|\Illuminate\Http\UploadedFile $files): array
    {
        $photoFiles = is_array($files) ? $files : [$files];

        return array_map(
            fn ($file) => Storage::url($file->store('vehicles', 'public')),
            $photoFiles,
        );
    }
}
