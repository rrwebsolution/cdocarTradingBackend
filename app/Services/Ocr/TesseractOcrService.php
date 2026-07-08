<?php

namespace App\Services\Ocr;

use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractOcrService
{
    /**
     * Run OCR over an image file and return the raw recognized text.
     *
     * @throws \Exception when the Tesseract binary is missing or the scan fails
     */
    public function recognize(string $imagePath): string
    {
        $preprocessedPath = $this->preprocess($imagePath);

        try {
            $ocr = new TesseractOCR($preprocessedPath ?? $imagePath);

            if ($path = config('services.tesseract.path')) {
                $ocr->executable($path);
            }

            // "Assume a single uniform block of text" — reads more cleanly
            // than the default fully-automatic segmentation for a document
            // photographed against a busy background.
            $ocr->psm(6);

            return trim($ocr->run());
        } finally {
            if ($preprocessedPath && is_file($preprocessedPath)) {
                unlink($preprocessedPath);
            }
        }
    }

    /**
     * Grayscale + contrast-stretch the image before handing it to Tesseract.
     * Phone photos of ID cards (glare, uneven lighting, low contrast against
     * skin/background) recognize noticeably better after this than the raw
     * photo. Returns null (falls back to the original file) if GD isn't
     * available or the image can't be decoded.
     */
    private function preprocess(string $imagePath): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $image = @imagecreatefromstring((string) file_get_contents($imagePath));

        if (! $image) {
            return null;
        }

        imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagefilter($image, IMG_FILTER_CONTRAST, -25);

        $outputPath = $imagePath.'.ocr-preprocessed.png';
        imagepng($image, $outputPath);
        imagedestroy($image);

        return $outputPath;
    }
}
