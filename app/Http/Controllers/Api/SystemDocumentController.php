<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\CrudResponses;
use App\Http\Controllers\Controller;
use App\Models\SystemDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SystemDocumentController extends Controller
{
    use CrudResponses;

    public function index(): JsonResponse
    {
        return $this->indexResponse(SystemDocument::class, ['customer', 'documentable']);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('file')) {
            $validated['file_url'] = Storage::url($request->file('file')->store('documents', 'public'));
            $validated['uploaded_at'] ??= now()->toDateString();
        }

        $document = SystemDocument::create($validated);

        return $this->storedResponse($document->load(['customer', 'documentable']));
    }

    public function show(SystemDocument $systemDocument): JsonResponse
    {
        return $this->showResponse($systemDocument->load(['customer', 'documentable']));
    }

    public function update(Request $request, SystemDocument $systemDocument): JsonResponse
    {
        $validated = $this->validated($request, $systemDocument);

        if ($request->hasFile('file')) {
            $validated['file_url'] = Storage::url($request->file('file')->store('documents', 'public'));
            $validated['uploaded_at'] ??= now()->toDateString();
        }

        $systemDocument->update($validated);

        return $this->updatedResponse($systemDocument->fresh(['customer', 'documentable']));
    }

    public function destroy(SystemDocument $systemDocument): JsonResponse
    {
        $systemDocument->delete();

        return $this->deletedResponse();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?SystemDocument $systemDocument = null): array
    {
        return $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'documentable_id' => ['nullable', 'integer'],
            'documentable_type' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'max:10240'],
            'file_url' => ['nullable', 'string', 'max:2048'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'reference' => [$systemDocument ? 'sometimes' : 'required', 'string', 'max:255', Rule::unique('system_documents', 'reference')->ignore($systemDocument?->id)],
            'remarks' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:255'],
            'title' => [$systemDocument ? 'sometimes' : 'required', 'string', 'max:255'],
            'type' => [$systemDocument ? 'sometimes' : 'required', 'string', 'max:255'],
            'uploaded_at' => ['nullable', 'date'],
            'verified_at' => ['nullable', 'date'],
            'verified_by' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
