<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::query()
                ->with('role:id,name')
                ->select(['id', 'role_id', 'name', 'email', 'status', 'created_at'])
                ->orderBy('id')
                ->get()
                ->map(fn (User $user) => $this->userPayload($user)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'message' => 'User created successfully.',
            'data' => $this->userPayload($user->load('role')),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['sometimes', 'required', 'exists:roles,id'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        if (array_key_exists('password', $validated) && ! $validated['password']) {
            unset($validated['password']);
        }

        if (array_key_exists('password', $validated)) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $this->userPayload($user->fresh('role')),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name ?? 'Unassigned',
            'role_id' => $user->role_id,
            'status' => $user->status ?? 'active',
            'created_at' => $user->created_at?->toDateTimeString(),
        ];
    }
}
