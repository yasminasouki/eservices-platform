<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class IdVerificationService
{
    /**
     * Submit an uploaded ID document for verification.
     *
     * Stores the request as 'pending' and logs the submission.
     * Connect to a real ID verification API (e.g., Onfido, Jumio, Stripe Identity)
     * by replacing the placeholder below with an actual HTTP call.
     *
     * Expected API response: extracted name, date of birth, document number, status.
     */
    public function submitForVerification(string $documentPath): void
    {
        // TODO: Integrate with your chosen ID verification API:
        //
        // $response = Http::withToken(config('services.id_verify.key'))
        //     ->post(config('services.id_verify.url'), [
        //         'document_url' => Storage::url($documentPath),
        //     ]);
        //
        // if ($response->successful()) {
        //     return $response->json(); // { name, dob, document_number, status }
        // }

        Log::info('ID verification submitted, awaiting admin review.', [
            'document_path' => $documentPath,
        ]);
    }
}
