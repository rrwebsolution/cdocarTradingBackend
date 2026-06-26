<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(Customer::class, ['user:id,name,email,status']);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storedResponse(Customer::create($this->validated($request)));
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $this->validated($request, true);

        $customer->update($validated);

        if (array_key_exists('status', $validated) && $customer->user) {
            $normalizedStatus = strtolower((string) $validated['status']);

            $customer->user->update([
                'status' => in_array($normalizedStatus, ['active', 'approved'], true)
                    ? 'active'
                    : 'inactive',
            ]);
        }

        return $this->updatedResponse($customer->fresh('user'));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false): array
    {
        return $request->validate([
            'address' => ['nullable', 'string'],
            'contact' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
            'valid_id_number' => ['nullable', 'string', 'max:255'],
            'valid_id_type' => ['nullable', 'string', 'max:255'],
            'valid_id_url' => ['nullable', 'string', 'max:2048'],
        ]);
    }
}
