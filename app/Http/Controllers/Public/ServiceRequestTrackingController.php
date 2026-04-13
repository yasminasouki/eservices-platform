<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class ServiceRequestTrackingController extends Controller
{
    /**
     * Public, no-login status page for a service request (scanned from QR or opened by URL).
     * Does not expose citizen PII or internal notes.
     */
    public function show(string $token): View
    {
        $token = trim($token);

        if (strlen($token) < 8 || strlen($token) > 80) {
            abort(404);
        }

        $serviceRequest = ServiceRequest::query()
            ->where('qr_code', $token)
            ->with([
                'service:id,name,price',
                'governmentOffice:id,name',
                'payment:id,service_request_id,status',
            ])
            ->firstOrFail();

        return view('public.service-request-track', [
            'request' => $serviceRequest,
            'awaitingPayment' => $serviceRequest->submitted_at === null && $serviceRequest->requiresCitizenPayment(),
        ]);
    }
}
