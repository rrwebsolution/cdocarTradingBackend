<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\SystemDocument;
use App\Models\Vehicle;
use App\Rules\VehicleIsAvailable;
use App\Support\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(Reservation::class, ['customer', 'vehicle']);
    }

    /**
     * Cash reservations are created in a single step: reservation details,
     * supporting documents, and proof of payment all arrive together, and the
     * vehicle is locked immediately (no separate reserve -> confirm stages).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);
        $validated['status'] ??= 'reserved';

        if ($request->hasFile('proof_of_payment')) {
            $validated['proof_of_payment_url'] = Storage::url(
                $request->file('proof_of_payment')->store('reservations', 'public')
            );
        }

        $reservation = DB::transaction(function () use ($validated, $request) {
            $vehicle = Vehicle::whereKey($validated['vehicle_id'])->lockForUpdate()->firstOrFail();

            $reservation = Reservation::create($validated);

            $vehicle->update(['status' => 'reserved']);

            if ($request->hasFile('documents')) {
                foreach ((array) $request->file('documents') as $file) {
                    SystemDocument::create([
                        'reference' => 'DOC-'.Str::upper(Str::random(10)),
                        'documentable_type' => Reservation::class,
                        'documentable_id' => $reservation->id,
                        'customer_id' => $reservation->customer_id,
                        'title' => 'Reservation requirement',
                        'type' => 'reservation_requirement',
                        'file_url' => Storage::url($file->store('documents', 'public')),
                        'uploaded_at' => now()->toDateString(),
                    ]);
                }
            }

            return $reservation;
        });

        return $this->storedResponse($reservation->load(['customer', 'vehicle']));
    }

    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $validated = $this->validated($request, true);

        if ($request->hasFile('proof_of_payment')) {
            $validated['proof_of_payment_url'] = Storage::url(
                $request->file('proof_of_payment')->store('reservations', 'public')
            );
        }

        $reservation->update($validated);

        if (in_array($validated['status'] ?? null, ['rejected', 'cancelled'], true)) {
            $reservation->vehicle()->update(['status' => 'available']);
        }

        return $this->updatedResponse($reservation->fresh(['customer', 'vehicle']));
    }

    public function destroy(Reservation $reservation): JsonResponse
    {
        $reservation->delete();

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => [$updating ? 'sometimes' : 'required', 'exists:customers,id'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'expires_at' => ['nullable', 'date'],
            'payment_method' => [$updating ? 'sometimes' : 'required', Rule::in(PaymentMethod::ALL)],
            'payment_reference_number' => ['nullable', 'string', 'max:255'],
            'proof_of_payment' => [$updating ? 'nullable' : 'required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'proof_of_payment_url' => ['nullable', 'string', 'max:2048'],
            'reference' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'reserved_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => array_filter([
                $updating ? 'sometimes' : 'required',
                'exists:vehicles,id',
                $updating ? null : new VehicleIsAvailable,
            ]),
            'verified_at' => ['nullable', 'date'],
            'verified_by' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
