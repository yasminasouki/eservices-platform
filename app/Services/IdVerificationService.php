<?php

namespace App\Services;

use App\Models\IdVerificationRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IdVerificationService
{
    /**
     * Submit front and back ID documents for OCR verification.
     *
     * Sends both sides to OCR.space, merges all extracted fields,
     * persists an IdVerificationRequest record, and returns the data.
     */
    public function submitForVerification(string $frontPath, string $backPath): array
    {
        [$frontText, $frontResponse] = $this->ocr($frontPath);
        [$backText,  $backResponse] = $this->ocr($backPath);

        $extracted = $this->parseIdText($frontText, $backText);

        IdVerificationRequest::create([
            'user_id' => auth()->id(),
            'id_document_path' => $frontPath,
            'id_document_back_path' => $backPath,
            'api_provider' => 'ocr_space',
            'api_response' => $frontResponse,
            'api_response_back' => $backResponse,
            'extracted_name' => $extracted['extracted_name'],
            'extracted_father_name' => $extracted['extracted_father_name'],
            'extracted_mother_name' => $extracted['extracted_mother_name'],
            'extracted_place_of_birth' => $extracted['extracted_place_of_birth'],
            'extracted_gender' => $extracted['extracted_gender'],
            'extracted_dob' => $extracted['extracted_dob'],
            'extracted_id_number' => $extracted['extracted_id_number'],
            'extracted_registry_number' => $extracted['extracted_registry_number'],
            'extracted_issue_date' => $extracted['extracted_issue_date'],
            'extracted_expiry_date' => $extracted['extracted_expiry_date'],
            'extracted_blood_type' => $extracted['extracted_blood_type'],
            'extracted_marital_status' => $extracted['extracted_marital_status'],
            'extracted_locality' => $extracted['extracted_locality'],
            'extracted_governorate' => $extracted['extracted_governorate'],
            'extracted_district' => $extracted['extracted_district'],
            'status' => 'pending',
        ]);

        Log::info('ID verification submitted via OCR.space (front + back).', [
            'front_path' => $frontPath,
            'back_path' => $backPath,
            'extracted' => $extracted,
        ]);

        return array_merge($extracted, ['status' => 'pending']);
    }

    // ──────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Call OCR.space for a single document and return [parsedText, rawResponse].
     */
    private function ocr(string $documentPath): array
    {
        [$text, $data] = $this->requestOcrSpace($documentPath, '1');

        $minChars = (int) config('services.ocr_space.fallback_min_chars', 50);
        if ($minChars > 0 && strlen(trim($text)) < $minChars) {
            [$text2, $data2] = $this->requestOcrSpace($documentPath, '2');
            if (strlen(trim($text2)) > strlen(trim($text))) {
                return [$text2, $data2];
            }
        }

        return [$text, $data];
    }

    /**
     * Single OCR.space request. Merges every ParsedResults block (multi-region / multi-page).
     */
    private function requestOcrSpace(string $documentPath, string $ocrEngine): array
    {
        $absolutePath = Storage::path($documentPath);
        $connectTimeout = (int) config('services.ocr_space.connect_timeout', 30);

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $filetype = match ($ext) {
            'pdf' => 'PDF',
            'png' => 'PNG',
            'gif' => 'GIF',
            'jpg', 'jpeg' => 'JPG',
            default => 'AUTO',
        };

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            Log::error('ID OCR: could not open file.', ['path' => $documentPath]);

            return ['', []];
        }

        try {
            $response = Http::timeout(90)
                ->connectTimeout($connectTimeout)
                ->asMultipart()
                ->post(config('services.ocr_space.url'), [
                    ['name' => 'apikey',             'contents' => config('services.ocr_space.key')],
                    ['name' => 'language',           'contents' => 'ara'],
                    ['name' => 'isOverlayRequired',  'contents' => 'false'],
                    ['name' => 'OCREngine',          'contents' => $ocrEngine],
                    ['name' => 'scale',              'contents' => 'true'],
                    ['name' => 'detectOrientation',  'contents' => 'true'],
                    ['name' => 'filetype',           'contents' => $filetype],
                    [
                        'name' => 'file',
                        'contents' => $handle,
                        'filename' => basename($absolutePath),
                    ],
                ]);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $responseData = $response->json() ?? [];

        // Missing key must mean "not errored" — (?? true) wrongly discarded good responses.
        $processingFailed = filter_var($responseData['IsErroredOnProcessing'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $parsedText = '';
        if ($response->successful() && ! $processingFailed) {
            foreach ($responseData['ParsedResults'] ?? [] as $block) {
                $t = $block['ParsedText'] ?? '';
                if ($t !== '') {
                    $parsedText .= ($parsedText === '' ? '' : "\n").$t;
                }
            }
        } else {
            Log::warning('OCR.space API error', [
                'status' => $response->status(),
                'engine' => $ocrEngine,
                'error_message' => $responseData['ErrorMessage'] ?? null,
                'document_path' => $documentPath,
            ]);
        }

        return [$parsedText, $responseData];
    }

    /**
     * Parse OCR text from both sides of a Lebanese Arabic national ID.
     *
     * Front fields extracted:
     *   الاسم          → first name
     *   الشهرة         → last name
     *   اسم الأب       → father's name
     *   اسم الأم       → mother's name
     *   تاريخ الولادة  → date of birth
     *   مكان الولادة   → place of birth (locality)
     *   الجنس          → gender
     *   فصيلة الدم     → blood type
     *   الحالة الاجتماعية → marital status
     *
     * Back fields extracted:
     *   الرقم          → ID number (numeric block ≥ 6 digits)
     *   رقم السجل      → registry number
     *   القضاء         → district
     *   المحافظة       → governorate
     *   البلدة / البلد → locality / town
     *   تاريخ الإصدار  → issue date
     *   تاريخ الانتهاء → expiry date
     */
    /**
     * Parse OCR text from both sides of a Lebanese Arabic national ID.
     *
     * Patterns are written to tolerate common OCR noise on Lebanese IDs:
     *  - Colons `:` OCR'd as periods `.`
     *  - Hamza dropped:  الأب → الاب,  الأم → الام
     *  - Label garbling: تاريخ → ربح,  المحافظة → اتحافطة, etc.
     *  - Arabic-Indic digits mixed with Latin digits
     */
    private function parseIdText(string $frontText, string $backText): array
    {
        $arabicIndic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $normaliseDigits = static fn (string $s): string => str_replace($arabicIndic, $western, $s);
        // Collapse inner whitespace to single space
        $collapse = static fn (string $s): string => trim(preg_replace('/\s+/', ' ', $s));

        // ── FRONT SIDE ────────────────────────────────────────────────────────

        // First name: الاسم (OCR sometimes gives الاسه)
        $firstName = $this->match('/الاس[مه]\s*[.:]\s*([\p{Arabic}]+)/u', $frontText);

        // Last name: الشهرة
        $lastName = $this->match('/الشهرة\s*[.:]\s*([\p{Arabic}]+)/u', $frontText);

        // Father name: اسم الاب (no hamza in OCR output)
        $fatherName = $this->match('/اسم\s+الا[بأ]\s*[.:]\s*([\p{Arabic}]+)/u', $frontText);

        // Mother name + surname: اسم الام وشهرتها (OCR: الاد وشهرق...<value> with no clean separator)
        $motherName = null;
        if (preg_match('/(?:اسم\s+الا[مد]|الا[مدظ])\s*(?:وشهر\S+)\s*[.:،]?\s*([\p{Arabic}][^\n]+)/u', $frontText, $m)) {
            $motherName = $collapse($m[1]);
        } elseif (preg_match('/(?:اسم\s+الا[مد])\s*[.:،]\s*([\p{Arabic}][^\n]+)/u', $frontText, $m)) {
            $motherName = $collapse($m[1]);
        }

        // Place of birth: محل الولادة
        $placeOfBirth = null;
        if (preg_match('/محل\s+الولادة\s*[.:]\s*([\p{Arabic}\s]+)/u', $frontText, $m)) {
            // First word/phrase only (stop at newline)
            $placeOfBirth = $collapse(explode("\n", $m[1])[0]);
        }

        // Date of birth: تاريخ الولادة (OCR: ربح الولا دة) — stop at newline
        $dob = null;
        if (preg_match('/(?:تاريخ|ربح|تار[يب]خ)\s+الولا\s*دة\s*[.:،]?\s*([٠-٩0-9 .\/\-]+)/u', $frontText, $m)) {
            $raw = preg_replace('/\s+/', '', $m[1]); // strip inner spaces within digits
            $dob = $normaliseDigits($raw) ?: null;
        }

        // ID number: large Arabic-Indic digit block on front (≥ 8 digits)
        $idNumber = null;
        if (preg_match('/([٠-٩]{6,15})/u', $frontText, $m)) {
            $idNumber = $normaliseDigits($m[1]);
        } elseif (preg_match('/\b(\d{6,15})\b/', $frontText, $m)) {
            $idNumber = $m[1];
        }

        // ── BACK SIDE ─────────────────────────────────────────────────────────

        // Gender: الجنس (OCR: الجنس. النى)
        $gender = $this->match('/الجنس\s*[.:]\s*([\p{Arabic}]+)/u', $backText);

        // Marital status: الوضع العائلي (may span two lines: "متأهلة من\nعزام السوقي")
        $marital = null;
        if (preg_match('/الوضع\s+العائل[يى]\s+([\p{Arabic}\s]+)/u', $backText, $m)) {
            $lines = array_map('trim', explode("\n", trim($m[1])));
            // Take up to 2 lines (name of spouse is on the second line)
            $marital = $collapse(implode(' ', array_filter(array_slice($lines, 0, 2))));
        }

        // Blood type: فئة الدم (OCR: فئة الدم: بA — extracts Latin letters + +/-)
        $bloodType = null;
        if (preg_match('/فئة\s+الدم\s*[.:]\s*([^\n]+)/u', $backText, $m)) {
            $raw = $m[1];
            // Find ABO type
            preg_match('/\b(AB|A|B|O)\b/i', $raw, $t);
            $type = isset($t[1]) ? strtoupper($t[1]) : (preg_match('/[A-Z]/i', $raw, $t2) ? strtoupper($t2[0]) : '');
            $rh = preg_match('/[+\-]/', $raw, $r) ? $r[0] : '';
            $bloodType = ($type.$rh) ?: null;
        }

        // Issue date: تاريخ الإصدار (OCR: تاربخ الاصدار: — value often garbled)
        // Also try the first digit-slash sequence at the very top of back text
        $issueDate = null;
        if (preg_match('/(?:تاريخ|تاربخ)\s+(?:الإصدار|الاصدار)\s*[.:،]?\s*([\p{Arabic}٠-٩0-9\s.\/\-]+)/u', $backText, $m)) {
            $raw = $normaliseDigits(preg_replace('/\s+/', '', $m[1]));
            if (strlen(preg_replace('/\D/', '', $raw)) >= 6) {
                $issueDate = $raw;
            }
        }
        if (! $issueDate) {
            // Fallback: first Arabic-Indic date at top of back (e.g. "٢/٠٥/١ ٢ ٠ ٢")
            $firstLine = explode("\n", trim($backText))[0] ?? '';
            if (preg_match('/([٠-٩][٠-٩\/\s]{4,}[٠-٩])/u', $firstLine, $m)) {
                $raw = $normaliseDigits(preg_replace('/\s+/', '', $m[1]));
                if (strlen(preg_replace('/\D/', '', $raw)) >= 6) {
                    $issueDate = $raw;
                }
            }
        }

        // Registry number: رقم السجل (OCR: رق الجل. ٣٣٦)
        $registryNumber = null;
        if (preg_match('/(?:رقم?|ر[قف])\s*(?:الس?جل?|الج[لن])\s*[.:،]?\s*([\p{Arabic}٠-٩0-9]+)/u', $backText, $m)) {
            $registryNumber = $normaliseDigits(trim($m[1]));
        }

        // Locality: المحلة أو القرية (OCR: اتخلة أو القة)
        $locality = null;
        if (preg_match('/(?:المحل[ةه]|اتخلة)\s+(?:أو|او)\s+(?:القرية|الق[ةه])\s*[.:،]?\s*([\p{Arabic}\s]+)/u', $backText, $m)) {
            $locality = $collapse(explode("\n", $m[1])[0]);
        }

        // Governorate: المحافظة (OCR: اتحافطة)
        $governorate = null;
        if (preg_match('/(?:المحافظة|اتحافطة|المحاف[ظط]ة)\s*[.:،]?\s*([\p{Arabic}\s]+)/u', $backText, $m)) {
            $governorate = $collapse(explode("\n", $m[1])[0]);
        }

        // District: القضاء
        $district = null;
        if (preg_match('/القضاء\s*[.:،]?\s*([\p{Arabic}]+)/u', $backText, $m)) {
            $district = trim($m[1]);
        }

        $fullName = implode(' ', array_filter([$firstName, $lastName])) ?: null;

        return [
            'extracted_name' => $fullName,
            'extracted_father_name' => $fatherName,
            'extracted_mother_name' => $motherName,
            'extracted_place_of_birth' => $placeOfBirth,
            'extracted_gender' => $gender,
            'extracted_dob' => $this->reconstructDate($dob),
            'extracted_id_number' => $idNumber,
            'extracted_registry_number' => $registryNumber,
            'extracted_issue_date' => $this->reconstructDate($issueDate),
            'extracted_expiry_date' => null, // not present on Lebanese IDs
            'extracted_blood_type' => $bloodType,
            'extracted_marital_status' => $marital,
            'extracted_locality' => $locality,
            'extracted_governorate' => $governorate,
            'extracted_district' => $district,
        ];
    }

    /**
     * Try to reconstruct a valid Y-m-d date from a garbled OCR digit string.
     * Handles RTL OCR noise where year/month/day may be out of order.
     * Returns null if a valid date cannot be determined.
     */
    private function reconstructDate(?string $raw): ?string
    {
        if (! $raw) {
            return null;
        }

        // Keep only digits and separators
        $cleaned = preg_replace('/[^\d\/.\-]/', '', $raw);
        $parts = array_filter(preg_split('/[\/.\-]/', $cleaned));

        $year = null;
        $rest = [];

        foreach ($parts as $part) {
            // Extract 4-digit year from within potentially garbled part (e.g. "19811")
            if (preg_match('/((?:19|20)\d{2})/', $part, $y)) {
                $year = $y[1];
                $rem = str_replace($year, '', $part);
                if (strlen($rem) > 1) {
                    $rest[] = $rem;
                } // skip 1-char artifacts
            } else {
                $rest[] = $part;
            }
        }

        if (! $year) {
            return null;
        }

        // Identify month (1–12) and day (1–31) from remaining parts
        $month = null;
        $day = null;
        foreach ($rest as $part) {
            $n = (int) $part;
            if ($n >= 1 && $n <= 12 && $month === null) {
                $month = str_pad((string) $n, 2, '0', STR_PAD_LEFT);
            } elseif ($n >= 1 && $n <= 31 && $day === null) {
                $day = str_pad((string) $n, 2, '0', STR_PAD_LEFT);
            }
        }

        if ($year && $month && $day) {
            return "$year-$month-$day";
        }
        if ($year && $month) {
            return "$year-$month-01";
        }

        return null;
    }

    /** Run a single regex match and return the first capture group or null. */
    private function match(string $pattern, string $subject): ?string
    {
        return preg_match($pattern, $subject, $m) ? trim($m[1]) : null;
    }
}
