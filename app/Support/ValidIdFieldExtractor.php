<?php

namespace App\Support;

class ValidIdFieldExtractor
{
    /**
     * ID-number patterns for common Philippine government IDs.
     *
     * @var array<string, string>
     */
    private const ID_NUMBER_PATTERNS = [
        // [0O] tolerates Tesseract's common misread of a leading zero as
        // the letter O (e.g. "K01-26-001045" read as "KO1-26-001045").
        'umid' => '/\b\d{4}-\d{7}-\d\b/',
        'drivers_license' => '/\b[A-Z][0-9O]\d-\d{2}-\d{6}\b/',
        'philid' => '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/',
        'passport' => '/\b[A-Z]{1,2}\d{7}\b/',
        'sss' => '/\b\d{2}-\d{7}-\d\b/',
        'tin' => '/\b\d{3}-\d{3}-\d{3}(-\d{3})?\b/',
    ];

    /**
     * Bilingual/alternate label spellings seen on common PH government IDs.
     * Matched as a whole line (e.g. "Apelyido/Last Name") since PhilID and
     * driver's license layouts print the label on its own line, with the
     * actual value on the line(s) that follow.
     *
     * @var array<string, array<int, string>>
     */
    private const LAST_NAME_LABELS = ['last name', 'apelyido', 'apelyido/last name', 'apelyido / last name'];

    private const GIVEN_NAME_LABELS = ['given names', 'given name', 'first name', 'mga pangalan', 'mga pangalan/given names', 'mga pangalan / given names'];

    private const MIDDLE_NAME_LABELS = ['middle name', 'gitnang apelyido', 'gitnang apelyido/middle name', 'gitnang apelyido / middle name'];

    private const FULL_NAME_LABELS = ['name', 'full name', 'last name, first name, middle name', 'last name first name middle name'];

    private const ADDRESS_LABELS = ['address', 'tirahan', 'tirahan/address', 'tirahan / address'];

    /**
     * @return array{confidence: string, extracted: array{last_name: ?string, first_name: ?string, middle_name: ?string, address: ?string, valid_id_number: ?string, mobile_number: ?string, email: ?string}}
     */
    public function extract(string $rawText, ?string $validIdType = null): array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $rawText)), fn (string $line) => $line !== ''));

        $idNumber = $this->extractIdNumber($rawText, $validIdType);
        $nameParts = $this->extractNameParts($lines);
        $address = $this->extractValueForLabels($lines, self::ADDRESS_LABELS);
        $mobileNumber = $this->extractValueForLabels($lines, ['mobile', 'mobile number', 'contact', 'contact number'])
            ?? $this->extractMobileNumber($rawText);
        $email = $this->extractValueForLabels($lines, ['email', 'email address'])
            ?? $this->extractEmail($rawText);

        $confidence = ($idNumber || $nameParts['last_name'] || $nameParts['first_name'] || $address || $mobileNumber || $email) ? 'high' : 'low';

        return [
            'confidence' => $confidence,
            'extracted' => [
                'last_name' => $nameParts['last_name'],
                'first_name' => $nameParts['first_name'],
                'middle_name' => $nameParts['middle_name'],
                'address' => $address,
                'valid_id_number' => $idNumber,
                'mobile_number' => $mobileNumber,
                'email' => $email,
            ],
        ];
    }

    private function extractIdNumber(string $rawText, ?string $validIdType): ?string
    {
        $typeKey = $validIdType ? str_replace(' ', '_', strtolower($validIdType)) : null;

        if ($typeKey && isset(self::ID_NUMBER_PATTERNS[$typeKey]) && preg_match(self::ID_NUMBER_PATTERNS[$typeKey], $rawText, $match)) {
            return $this->normalizeIdNumber($match[0], $typeKey);
        }

        foreach (self::ID_NUMBER_PATTERNS as $key => $pattern) {
            if (preg_match($pattern, $rawText, $match)) {
                return $this->normalizeIdNumber($match[0], $key);
            }
        }

        return null;
    }

    /**
     * Driver's license numbers are letter+digits — Tesseract commonly
     * misreads a leading zero as the letter O (matched deliberately by the
     * pattern above), so swap it back once we know it's meant to be a digit.
     */
    private function normalizeIdNumber(string $value, string $type): string
    {
        if ($type !== 'drivers_license') {
            return $value;
        }

        return $value[0].str_replace(['O', 'o'], '0', substr($value, 1));
    }

    /**
     * Name part extraction: PhilID splits the name across three separate
     * labelled fields (Last Name / Given Names / Middle Name); driver's
     * licenses print one combined "Last, First Middle" value under a single
     * header line. Try the split-field case first, then fall back to
     * splitting a single combined name field.
     *
     * @param array<int, string> $lines
     * @return array{last_name: ?string, first_name: ?string, middle_name: ?string}
     */
    private function extractNameParts(array $lines): array
    {
        $lastName = $this->extractValueForLabels($lines, self::LAST_NAME_LABELS);
        $firstName = $this->extractValueForLabels($lines, self::GIVEN_NAME_LABELS);
        $middleName = $this->extractValueForLabels($lines, self::MIDDLE_NAME_LABELS);

        if ($lastName || $firstName || $middleName) {
            return ['last_name' => $lastName, 'first_name' => $firstName, 'middle_name' => $middleName];
        }

        $combined = $this->extractValueForLabels($lines, self::FULL_NAME_LABELS);

        return $combined ? $this->splitCombinedName($combined) : ['last_name' => null, 'first_name' => null, 'middle_name' => null];
    }

    /**
     * Splits a single "Last, First Middle" value (the layout driver's
     * licenses print the name in) into separate parts. The last word of the
     * remainder after the comma is treated as the middle name, matching how
     * PhilID prints these same three parts in separate fields.
     *
     * @return array{last_name: ?string, first_name: ?string, middle_name: ?string}
     */
    private function splitCombinedName(string $value): array
    {
        // Tesseract frequently misreads the comma after the last name as a
        // period, so accept either.
        if (! preg_match('/^(.+?)[,.]\s*(.+)$/', $value, $match)) {
            return ['last_name' => null, 'first_name' => $value, 'middle_name' => null];
        }

        $lastName = trim($match[1]);
        $remainder = preg_split('/\s+/', trim($match[2])) ?: [];

        if (count($remainder) > 1) {
            $middleName = array_pop($remainder);
            $firstName = implode(' ', $remainder);
        } else {
            $middleName = null;
            $firstName = $remainder[0] ?? null;
        }

        return ['last_name' => $lastName, 'first_name' => $firstName, 'middle_name' => $middleName];
    }

    /**
     * Finds a line matching one of the given labels (either "Label: Value"
     * on the same line, or a label-only line followed by its value on the
     * next non-empty line — the layout used by PhilID/driver's license).
     *
     * @param array<int, string> $lines
     * @param array<int, string> $labels
     */
    private function extractValueForLabels(array $lines, array $labels): ?string
    {
        foreach ($lines as $index => $line) {
            foreach ($labels as $label) {
                // Same-line "Label: Value" or "Label - Value".
                if (preg_match('/^'.preg_quote($label, '/').'\s*[:\-]\s*(.+)$/i', $line, $match)) {
                    return trim($match[1]);
                }

                // Label-only line; value is the next non-empty, non-label line.
                if ($this->normalizeLabel($line) === $this->normalizeLabel($label)) {
                    for ($next = $index + 1; $next < count($lines); $next++) {
                        $candidate = trim($lines[$next]);

                        if ($candidate === '') {
                            continue;
                        }

                        if ($this->looksLikeLabel($candidate)) {
                            break;
                        }

                        return $candidate;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Lowercases, collapses whitespace, and strips punctuation that
     * Tesseract commonly confuses (comma/period/dash) so a label line still
     * matches even if OCR misreads its punctuation.
     */
    private function normalizeLabel(string $value): string
    {
        $value = preg_replace('/[,.\-]/', '', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', strtolower($value)) ?? '');
    }

    /**
     * Heuristic: a line is probably another field label (not a value) if it
     * matches any of the known label phrases used across ID types.
     */
    private function looksLikeLabel(string $line): bool
    {
        $normalized = $this->normalizeLabel($line);
        $allLabels = array_merge(
            self::LAST_NAME_LABELS,
            self::GIVEN_NAME_LABELS,
            self::MIDDLE_NAME_LABELS,
            self::FULL_NAME_LABELS,
            self::ADDRESS_LABELS,
            ['date of birth', 'petsa ng kapanganakan', 'nationality', 'sex', 'weight (kg)', 'height(m)', 'license no.', 'expiration date', 'agency code'],
        );

        return in_array($normalized, array_map(fn (string $label) => $this->normalizeLabel($label), $allLabels), true);
    }

    private function extractMobileNumber(string $rawText): ?string
    {
        if (preg_match('/\b(?:\+63|0)9\d{2}[\s-]?\d{3}[\s-]?\d{4}\b/', $rawText, $match)) {
            return $match[0];
        }

        return null;
    }

    private function extractEmail(string $rawText): ?string
    {
        if (preg_match('/\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b/', $rawText, $match)) {
            return $match[0];
        }

        return null;
    }
}
