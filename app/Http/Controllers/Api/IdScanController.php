<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ocr\TesseractOcrService;
use App\Support\ValidIdFieldExtractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class IdScanController extends Controller
{
    public function __construct(
        private readonly TesseractOcrService $ocr,
        private readonly ValidIdFieldExtractor $extractor,
    ) {}

    /**
     * Scan an uploaded valid ID and return best-effort extracted fields for the
     * registration form to prefill. Stateless — nothing is persisted here, and
     * OCR failures degrade gracefully so the customer can still type manually.
     *
     * @throws ValidationException
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'valid_id_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'valid_id_type' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('valid_id_file');

        if ($file->getClientOriginalExtension() === 'pdf' || $file->getMimeType() === 'application/pdf') {
            return response()->json([
                'message' => 'OCR is not supported for PDF uploads. Please fill in the fields manually.',
                'data' => ['ocr_supported' => false, 'confidence' => 'low', 'extracted' => null],
            ]);
        }

        try {
            $rawText = $this->ocr->recognize($file->getRealPath());
            $result = $this->extractor->extract($rawText, $request->string('valid_id_type')->toString() ?: null);
        } catch (Throwable) {
            $result = ['confidence' => 'low', 'extracted' => [
                'last_name' => null,
                'first_name' => null,
                'middle_name' => null,
                'address' => null,
                'valid_id_number' => null,
                'mobile_number' => null,
                'email' => null,
            ]];
        }

        return response()->json([
            'message' => 'ID scanned.',
            'data' => array_merge(['ocr_supported' => true], $result),
        ]);
    }
}
