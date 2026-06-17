<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Role::query()
                ->select(['id', 'name', 'description', 'status', 'permissions'])
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'] ?? [],
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => $role,
        ], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['sometimes', 'nullable', 'string'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'max:255'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => $role->fresh(['users']),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'This role cannot be deleted because it is assigned to one or more users.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }
}
