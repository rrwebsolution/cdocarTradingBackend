<?php

namespace App\Support;

final class PdfLogo
{
    /**
     * Base64-encode the company logo for embedding in dompdf views (which
     * require local/embedded images, not remote URLs). Returns null if no
     * logo has been placed at public/images/logo.png yet.
     */
    public static function base64(): ?string
    {
        $path = public_path('images/logo.png');

        if (! is_file($path)) {
            return null;
        }

        return base64_encode(file_get_contents($path));
    }
}
