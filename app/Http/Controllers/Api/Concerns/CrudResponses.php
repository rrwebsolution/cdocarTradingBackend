<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

trait CrudResponses
{
    /**
     * @param array<int, string> $relations
     */
    protected function indexResponse(string $modelClass, array $relations = []): JsonResponse
    {
        return response()->json([
            'data' => $modelClass::query()
                ->with($relations)
                ->latest('id')
                ->get(),
        ]);
    }

    protected function showResponse(Model $model): JsonResponse
    {
        return response()->json(['data' => $model]);
    }

    protected function storedResponse(Model $model): JsonResponse
    {
        return response()->json([
            'message' => 'Record created successfully.',
            'data' => $model,
        ], 201);
    }

    protected function updatedResponse(Model $model): JsonResponse
    {
        return response()->json([
            'message' => 'Record updated successfully.',
            'data' => $model,
        ]);
    }

    protected function deletedResponse(): JsonResponse
    {
        return response()->json(['message' => 'Record deleted successfully.']);
    }
}
