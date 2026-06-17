<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(Staff::class, ['user:id,name,email']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);
        $role = Role::query()->where('name', $validated['position'])->first();

        if (! $role) {
            return response()->json([
                'message' => 'The selected Position/Roles does not exist.',
                'errors' => [
                    'position' => ['The selected Position/Roles does not exist.'],
                ],
            ], 422);
        }

        $staff = DB::transaction(function () use ($validated, $role) {
            $user = User::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'password' => Hash::make('password123'),
                'role_id' => $role->id,
                'status' => $validated['status'] ?? 'active',
            ]);

            return Staff::create([
                ...$validated,
                'user_id' => $user->id,
            ]);
        });

        return $this->storedResponse($staff->fresh('user'));
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $validated = $this->validated($request, true, $staff);

        DB::transaction(function () use ($staff, $validated) {
            $staff->update($validated);

            $user = $staff->user;

            if (! $user && $staff->email) {
                $role = Role::query()->where('name', $staff->position)->first();

                if ($role) {
                    $user = User::create([
                        'email' => $staff->email,
                        'name' => $staff->name,
                        'password' => Hash::make('password123'),
                        'role_id' => $role->id,
                        'status' => $staff->status ?? 'active',
                    ]);

                    $staff->update(['user_id' => $user->id]);
                }
            }

            if ($user) {
                $userUpdates = [];

                if (array_key_exists('name', $validated)) {
                    $userUpdates['name'] = $validated['name'];
                }

                if (array_key_exists('email', $validated)) {
                    $userUpdates['email'] = $validated['email'];
                }

                if (array_key_exists('status', $validated)) {
                    $userUpdates['status'] = $validated['status'] ?? 'active';
                }

                if (array_key_exists('position', $validated)) {
                    $role = Role::query()->where('name', $validated['position'])->first();

                    if ($role) {
                        $userUpdates['role_id'] = $role->id;
                    }
                }

                if ($userUpdates) {
                    $user->update($userUpdates);
                }
            }
        });

        return $this->updatedResponse($staff->fresh('user'));
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $staff->delete();

        return $this->deletedResponse();
    }

    private function validated(Request $request, bool $updating = false, ?Staff $staff = null): array
    {
        return $request->validate([
            'activity' => ['nullable', 'string', 'max:255'],
            'contact' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'email' => [
                $updating ? 'sometimes' : 'required',
                'email',
                'max:255',
                Rule::unique('staff', 'email')->ignore($staff?->id),
                Rule::unique('users', 'email')->ignore($staff?->user_id),
            ],
            'name' => [$updating ? 'sometimes' : 'required', 'string', 'max:255'],
            'position' => [
                $updating ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::exists('roles', 'name'),
            ],
            'schedule' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);
    }
}
