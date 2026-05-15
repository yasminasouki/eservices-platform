<?php

namespace App\DTOs;

readonly class DocumentValidationResult
{
    public function __construct(
        public bool $readable,
        public bool $expired,
        public bool $typeMatches,
        public int $confidence,
        public string $summary,
        public array $issues,
        public ?string $documentTypeFound = null,
    ) {}

    public function passes(): bool
    {
        return $this->readable && ! $this->expired && $this->typeMatches;
    }

    public function failureMessage(?string $expectedType = null): string
    {
        if (! $this->readable) {
            return 'This file appears unreadable or is not a valid document. Please upload a clear, well-lit scan or photo.';
        }

        if ($this->expired) {
            return 'This document appears to be expired. Please upload a current, valid version.';
        }

        if (! $this->typeMatches) {
            $expected = $expectedType ? " (\"{$expectedType}\")" : '';
            $found = $this->documentTypeFound ? " (found: {$this->documentTypeFound})" : '';
            return "This file does not appear to match the required document type{$expected}{$found}. Please upload the correct document.";
        }

        return 'Document validation failed. Please upload a valid document.';
    }
}
