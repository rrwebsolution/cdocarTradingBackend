<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(Reservation::class, ['customer', 'vehicle']);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storedResponse(Reservation::create($this->validated($request)));
    }

    public function update(Request $request, Reservation $reservation): JsonResponse
    {
        $reservation->update($this->validated($request, true));

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
            'expires_at' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'reserved_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => [$updating ? 'sometimes' : 'required', 'exists:vehicles,id'],
        ]);
    }
}
