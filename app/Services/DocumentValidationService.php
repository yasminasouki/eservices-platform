<?php

namespace App\Services;

use App\DTOs\DocumentValidationResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentValidationService
{
    // Minimum meaningful characters after stripping whitespace to consider a document readable.
    private const MIN_READABLE_CHARS = 25;

    // OCR keywords that indicate an expiry/validity date follows nearby.
    private const EXPIRY_KEYWORDS = [
        // English
        'expires', 'expiry', 'expiration', 'valid until', 'valid through',
        'valid to', 'exp date', 'exp.', 'use before', 'best before',
        // French (common in Lebanese official docs)
        'expire', 'valable jusqu', 'date d\'expiration',
        // Arabic
        'انتهاء', 'صلاحية', 'نافذ حتى', 'تاريخ الانتهاء',
    ];

    // Keyword sets for common document types — used for type-match checking.
    // Keys are lowercase; values are words/phrases that should appear in OCR text.
    private const TYPE_KEYWORD_MAP = [
        'birth certificate'    => ['birth', 'born', 'date of birth', 'place of birth', 'ميلاد', 'شهادة ميلاد'],
        'national id'          => ['national', 'identity', 'identification', 'هوية', 'بطاقة هوية'],
        'passport'             => ['passport', 'travel', 'جواز', 'nationality', 'given name', 'surname'],
        'driving license'      => ['driver', 'driving', 'license', 'رخصة', 'قيادة'],
        'driver license'       => ['driver', 'driving', 'license', 'رخصة', 'قيادة'],
        'utility bill'         => ['bill', 'invoice', 'electricity', 'water', 'gas', 'فاتورة', 'amount due', 'kwh'],
        'proof of address'     => ['address', 'street', 'residence', 'عنوان', 'residing', 'domicile'],
        'bank statement'       => ['bank', 'statement', 'balance', 'account', 'transaction', 'بنك', 'حساب'],
        'marriage certificate' => ['marriage', 'married', 'spouse', 'زواج', 'عقد'],
        'property deed'        => ['property', 'deed', 'ownership', 'real estate', 'عقار', 'ملكية'],
        'income certificate'   => ['income', 'salary', 'employment', 'employer', 'راتب', 'دخل'],
        'tax certificate'      => ['tax', 'fiscal', 'revenue', 'ضريبة'],
        'no criminal record'   => ['criminal', 'police', 'clearance', 'record', 'عدلية', 'سوابق'],
        'medical certificate'  => ['medical', 'health', 'doctor', 'hospital', 'طبي', 'مستشفى', 'physician'],
        'insurance'            => ['insurance', 'policy', 'coverage', 'premium', 'تأمين'],
        'residence permit'     => ['residence', 'permit', 'residency', 'إقامة', 'resident'],
    ];

    public function validate(UploadedFile $file, ?string $expectedType = null): DocumentValidationResult
    {
        $apiKey = config('services.ocr_space.key');

        if (! $apiKey) {
            return $this->passthrough();
        }

        try {
            $text = $this->runOcr($file);
        } catch (\Throwable $e) {
            Log::warning('DocumentValidationService: OCR call failed — allowing upload', [
                'file'  => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            return $this->passthrough();
        }

        $readable   = $this->checkReadable($text);
        $expired    = $readable && $this->checkExpired($text);
        $typeMatches = ! $readable || ! $expectedType || $this->checkTypeMatch($text, $expectedType);

        $issues = [];
        if (! $readable)    $issues[] = 'Document could not be read by OCR.';
        if ($expired)       $issues[] = 'An expiry date before today was detected.';
        if (! $typeMatches) $issues[] = "Content does not match expected type \"{$expectedType}\".";

        return new DocumentValidationResult(
            readable:   $readable,
            expired:    $expired,
            typeMatches: $typeMatches,
            confidence: $readable ? 80 : 70,
            summary:    $readable ? mb_substr(trim(preg_replace('/\s+/', ' ', $text)), 0, 120) : 'No readable text extracted.',
            issues:     $issues,
        );
    }

    // ── OCR ──────────────────────────────────────────────────────────────────

    private function runOcr(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        $ext  = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        // Engine 2 (Tesseract) handles printed documents well; engine 1 as fallback for short results.
        $text = $this->requestOcrSpace($path, $ext, '2', 'eng');

        $minChars = (int) config('services.ocr_space.fallback_min_chars', 50);

        if (strlen(trim($text)) < $minChars) {
            // Retry with engine 1 and Arabic in case the doc is Arabic-only.
            $fallback = $this->requestOcrSpace($path, $ext, '1', 'ara');
            if (strlen(trim($fallback)) > strlen(trim($text))) {
                $text = $fallback;
            }
        }

        return $text;
    }

    private function requestOcrSpace(string $absolutePath, string $ext, string $engine, string $lang): string
    {
        $filetype = match ($ext) {
            'pdf'        => 'PDF',
            'png'        => 'PNG',
            'gif'        => 'GIF',
            'jpg', 'jpeg'=> 'JPG',
            'webp'       => 'JPG', // OCR.space treats WebP as JPG-compatible
            default      => 'AUTO',
        };

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            return '';
        }

        try {
            $connectTimeout = (int) config('services.ocr_space.connect_timeout', 30);

            $response = Http::timeout(60)
                ->connectTimeout($connectTimeout)
                ->asMultipart()
                ->post(config('services.ocr_space.url'), [
                    ['name' => 'apikey',            'contents' => config('services.ocr_space.key')],
                    ['name' => 'language',          'contents' => $lang],
                    ['name' => 'OCREngine',         'contents' => $engine],
                    ['name' => 'isOverlayRequired', 'contents' => 'false'],
                    ['name' => 'scale',             'contents' => 'true'],
                    ['name' => 'detectOrientation', 'contents' => 'true'],
                    ['name' => 'filetype',          'contents' => $filetype],
                    [
                        'name'     => 'file',
                        'contents' => $handle,
                        'filename' => basename($absolutePath),
                    ],
                ]);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $data             = $response->json() ?? [];
        $processingFailed = filter_var($data['IsErroredOnProcessing'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $response->successful() || $processingFailed) {
            Log::warning('DocumentValidationService: OCR.space error', [
                'status'  => $response->status(),
                'engine'  => $engine,
                'message' => $data['ErrorMessage'] ?? null,
            ]);
            return '';
        }

        $parsedText = '';
        foreach ($data['ParsedResults'] ?? [] as $block) {
            $t = $block['ParsedText'] ?? '';
            if ($t !== '') {
                $parsedText .= ($parsedText === '' ? '' : "\n").$t;
            }
        }

        return $parsedText;
    }

    // ── Checks ───────────────────────────────────────────────────────────────

    private function checkReadable(string $text): bool
    {
        // Strip whitespace and non-alphanumeric noise; count meaningful characters.
        $meaningful = preg_replace('/[\s\r\n\t]+/', '', $text);
        return strlen($meaningful) >= self::MIN_READABLE_CHARS;
    }

    private function checkExpired(string $text): bool
    {
        $lower = mb_strtolower($text);

        // Find any expiry keyword; then look for a date within the next 200 chars after it.
        foreach (self::EXPIRY_KEYWORDS as $keyword) {
            $pos = mb_strpos($lower, mb_strtolower($keyword));
            if ($pos === false) {
                continue;
            }

            $window = mb_substr($text, $pos, 200);
            $date   = $this->extractDate($window);

            if ($date && $date < now()->startOfDay()) {
                return true;
            }
        }

        return false;
    }

    private function checkTypeMatch(string $text, string $expectedType): bool
    {
        if (strlen(trim($text)) < 80) {
            // Not enough text to make a confident type judgement.
            return true;
        }

        $lower         = mb_strtolower($text);
        $expectedLower = mb_strtolower(trim($expectedType));

        // Find the closest curated keyword set.
        $keywords = $this->resolveKeywords($expectedLower);

        if (empty($keywords)) {
            // Unknown type — fall back to checking words from the type label itself.
            $keywords = $this->wordsFromLabel($expectedLower);
        }

        if (empty($keywords)) {
            return true;
        }

        foreach ($keywords as $kw) {
            if (mb_strpos($lower, mb_strtolower($kw)) !== false) {
                return true;
            }
        }

        // None of the expected keywords appeared in the document text.
        return false;
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Find the best curated keyword list for a given expected-type string.
     * Tries exact match first, then looks for any map key that appears inside
     * the expected-type string or vice-versa.
     */
    private function resolveKeywords(string $expectedLower): array
    {
        if (isset(self::TYPE_KEYWORD_MAP[$expectedLower])) {
            return self::TYPE_KEYWORD_MAP[$expectedLower];
        }

        foreach (self::TYPE_KEYWORD_MAP as $key => $kws) {
            if (str_contains($expectedLower, $key) || str_contains($key, $expectedLower)) {
                return $kws;
            }
        }

        return [];
    }

    /**
     * Split a label like "Proof of Income" into significant words,
     * stripping common stop-words.
     */
    private function wordsFromLabel(string $label): array
    {
        $stopWords = ['of', 'a', 'an', 'the', 'and', 'or', 'for', 'to', 'in', 'on', 'at', 'with'];
        $words     = preg_split('/\s+/', $label);

        return array_values(array_filter($words, fn ($w) => strlen($w) > 2 && ! in_array($w, $stopWords, true)));
    }

    /**
     * Extract the first recognisable date from a text window.
     * Supports: DD/MM/YYYY, MM/YYYY, YYYY-MM-DD, "Jan 2020", Arabic-Indic digits.
     */
    private function extractDate(string $window): ?\Illuminate\Support\Carbon
    {
        // Normalize Arabic-Indic digits to Western.
        $arabicIndic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $western     = ['0','1','2','3','4','5','6','7','8','9'];
        $window      = str_replace($arabicIndic, $western, $window);

        $patterns = [
            // DD/MM/YYYY or DD-MM-YYYY
            '/\b(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})\b/' => fn ($m) => now()->createFromDate((int)$m[3], (int)$m[2], (int)$m[1]),
            // MM/YYYY (expiry cards — treat as last day of that month)
            '/\b(\d{1,2})[\/\-](\d{4})\b/'                     => fn ($m) => now()->createFromDate((int)$m[2], (int)$m[1], 1)->endOfMonth(),
            // YYYY-MM-DD
            '/\b(\d{4})[\/\-](\d{2})[\/\-](\d{2})\b/'         => fn ($m) => now()->createFromDate((int)$m[1], (int)$m[2], (int)$m[3]),
            // "Jan 2025" / "January 2025"
            '/\b(jan(?:uary)?|feb(?:ruary)?|mar(?:ch)?|apr(?:il)?|may|jun(?:e)?|jul(?:y)?|aug(?:ust)?|sep(?:tember)?|oct(?:ober)?|nov(?:ember)?|dec(?:ember)?)\s+(\d{4})\b/i'
                => function ($m) {
                    $mon = date_parse($m[1])['month'];
                    return $mon ? now()->createFromDate((int)$m[2], $mon, 1)->endOfMonth() : null;
                },
        ];

        foreach ($patterns as $regex => $builder) {
            if (preg_match($regex, $window, $matches)) {
                try {
                    $date = $builder($matches);
                    if ($date && $date->year >= 2000 && $date->year <= 2100) {
                        return $date;
                    }
                } catch (\Throwable) {
                    // Unparseable date — try next pattern.
                }
            }
        }

        return null;
    }

    private function passthrough(): DocumentValidationResult
    {
        return new DocumentValidationResult(
            readable:    true,
            expired:     false,
            typeMatches: true,
            confidence:  0,
            summary:     '',
            issues:      [],
        );
    }
}
