<?php

namespace App\Services;

use App\Models\IdVerificationRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IdVerificationService
{
    /**
     * Submit an uploaded ID document for OCR verification via OCR.space.
     *
     * Sends the document to the OCR.space API, parses the returned text to
     * extract name / date-of-birth / ID number, persists an
     * IdVerificationRequest record, and returns the extracted data.
     *
     * @return array{extracted_name: string|null, extracted_dob: string|null, extracted_id_number: string|null, status: string}
     */
    public function submitForVerification(string $documentPath): array
    {
        $absolutePath = Storage::path($documentPath);

        $response = Http::asMultipart()
            ->post(config('services.ocr_space.url'), [
                [
                    'name'     => 'apikey',
                    'contents' => config('services.ocr_space.key'),
                ],
                [
                    'name'     => 'language',
                    'contents' => 'ara',
                ],
                [
                    'name'     => 'isOverlayRequired',
                    'contents' => 'false',
                ],
                [
                    'name'     => 'file',
                    'contents' => fopen($absolutePath, 'r'),
                    'filename' => basename($absolutePath),
                ],
            ]);

        $responseData = $response->json() ?? [];

        $parsedText = '';
        if ($response->successful() && ! ($responseData['IsErroredOnProcessing'] ?? true)) {
            $parsedText = $responseData['ParsedResults'][0]['ParsedText'] ?? '';
        } else {
            Log::warning('OCR.space API error', [
                'status'        => $response->status(),
                'error_message' => $responseData['ErrorMessage'] ?? null,
                'document_path' => $documentPath,
            ]);
        }

        $extracted = $this->parseIdText($parsedText);

        IdVerificationRequest::create([
            'user_id'              => auth()->id(),
            'id_document_path'     => $documentPath,
            'api_provider'         => 'ocr_space',
            'api_response'         => $responseData,
            'extracted_name'       => $extracted['extracted_name'],
            'extracted_dob'        => $extracted['extracted_dob'],
            'extracted_id_number'  => $extracted['extracted_id_number'],
            'status'               => 'pending',
        ]);

        Log::info('ID verification submitted via OCR.space, awaiting admin review.', [
            'document_path' => $documentPath,
            'extracted'     => $extracted,
        ]);

        return array_merge($extracted, ['status' => 'pending']);
    }

    /**
     * Parse raw OCR text from a Lebanese Arabic national ID.
     *
     * Looks for RTL-labelled fields produced by the Arabic OCR pass:
     *   الاسم :      → first name
     *   الشهرة :     → last name
     *   تاريخ الولادة : → date of birth (Arabic-Indic numerals)
     *   A standalone numeric sequence (≥ 6 digits) → ID number
     *
     * Arabic-Indic digits (٠١٢٣٤٥٦٧٨٩) are normalised to Western digits.
     *
     * @return array{extracted_name: string|null, extracted_dob: string|null, extracted_id_number: string|null}
     */
    private function parseIdText(string $text): array
    {
        $firstName = null;
        $lastName  = null;
        $dob       = null;
        $idNumber  = null;

        // Arabic-Indic → Western digit map
        $arabicIndic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $western     = ['0','1','2','3','4','5','6','7','8','9'];

        $normaliseDigits = static fn(string $s): string => str_replace($arabicIndic, $western, $s);

        // Arabic digit class for regex (covers both Unicode blocks)
        $ad = '٠-٩0-9';

        // ── First name: الاسم ──────────────────────────────────────────────
        if (preg_match('/الاسم\s+([\p{Arabic}]+)/u', $text, $m)) {
            $firstName = trim($m[1]);
        }

        // ── Last name: الشهرة ──────────────────────────────────────────────
        if (preg_match('/الشهرة\s+([\p{Arabic}]+)/u', $text, $m)) {
            $lastName = trim($m[1]);
        }

        // ── Date of birth: تاريخ الولادة ────────────────────────────────────
        // Accepts Arabic-Indic or Western digits, separated by / or .
        if (preg_match('/تاريخ\s+الولادة\s+([\p{Arabic}٠-٩0-9\.\/]+)/u', $text, $m)) {
            $dob = $normaliseDigits(trim($m[1]));
        }

        // ── ID number: standalone numeric block (≥ 6 digits, near page bottom) ─
        // Lebanese IDs carry a pure-digit sequence; try Western then Arabic-Indic
        if (preg_match('/\b(\d{6,12})\b/', $text, $m)) {
            $idNumber = $m[1];
        } elseif (preg_match('/([٠-٩]{6,12})/u', $text, $m)) {
            $idNumber = $normaliseDigits($m[1]);
        }

        $name = implode(' ', array_filter([$firstName, $lastName])) ?: null;

        return [
            'extracted_name'      => $name,
            'extracted_dob'       => $dob,
            'extracted_id_number' => $idNumber,
        ];
    }
}
