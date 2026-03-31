<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\GovernmentOffice;
use App\Models\ServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CitizenFeedbackController extends Controller
{
    public function createForRequest(ServiceRequest $serviceRequest): View
    {
        $this->assertOwnsRequest($serviceRequest);
        abort_unless($serviceRequest->status === 'completed', 403, 'You can leave feedback after the office marks this request as completed.');
        abort_if($serviceRequest->feedback()->exists(), 403, 'You have already submitted feedback for this request.');

        $serviceRequest->load(['governmentOffice:id,name', 'service:id,name']);

        return view('citizen.feedback.create', [
            'office' => $serviceRequest->governmentOffice,
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function storeForRequest(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->assertOwnsRequest($serviceRequest);
        abort_unless($serviceRequest->status === 'completed', 403);
        abort_if($serviceRequest->feedback()->exists(), 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'government_office_id' => $serviceRequest->government_office_id,
            'service_request_id' => $serviceRequest->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()
            ->route('citizen.requests.show', $serviceRequest)
            ->with('success', 'Thank you — your feedback helps improve public services.');
    }

    public function createForOffice(GovernmentOffice $office): View
    {
        abort_unless($office->is_active, 404);

        return view('citizen.feedback.create', [
            'office' => $office,
            'serviceRequest' => null,
        ]);
    }

    public function storeForOffice(Request $request, GovernmentOffice $office): RedirectResponse
    {
        abort_unless($office->is_active, 404);

        if ($request->input('comment') === '') {
            $request->merge(['comment' => null]);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Feedback::create([
            'user_id' => $request->user()->id,
            'government_office_id' => $office->id,
            'service_request_id' => null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()
            ->route('citizen.offices.show', $office)
            ->with('success', 'Thank you for your feedback.');
    }

    private function assertOwnsRequest(ServiceRequest $serviceRequest): void
    {
        abort_unless((int) $serviceRequest->user_id === (int) auth()->id(), 404);
    }
}
