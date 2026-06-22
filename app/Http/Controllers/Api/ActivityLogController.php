<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(ActivityLog::class, ['user', 'subject']);
    }

    public function store(Request $request): JsonResponse
    {
        $log = ActivityLog::create($this->validated($request));

        return $this->storedResponse($log->load(['user', 'subject']));
    }

    public function show(ActivityLog $activityLog): JsonResponse
    {
        return $this->showResponse($activityLog->load(['user', 'subject']));
    }

    public function update(Request $request, ActivityLog $activityLog): JsonResponse
    {
        $activityLog->update($this->validated($request));

        return $this->updatedResponse($activityLog->fresh(['user', 'subject']));
    }

    public function destroy(ActivityLog $activityLog): JsonResponse
    {
        $activityLog->delete();

        return $this->deletedResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'action' => ['required', 'string', 'max:255'],
            'actor_name' => ['nullable', 'string', 'max:255'],
            'logged_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
            'module' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);
    }
}
