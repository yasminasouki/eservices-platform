<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\GovernmentOffice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeFeedbackController extends Controller
{
    public function index(Request $request, GovernmentOffice $office): View
    {
        if ($request->input('replied') === '') {
            $request->merge(['replied' => null]);
        }

        $validated = $request->validate([
            'replied' => ['nullable', 'string', 'in:all,yes,no'],
        ]);

        $repliedFilter = $validated['replied'] ?? 'all';

        $query = Feedback::query()
            ->where('government_office_id', $office->id)
            ->with([
                'citizen:id,name,email',
                'serviceRequest:id',
            ]);

        if ($repliedFilter === 'yes') {
            $query->whereNotNull('replied_at');
        } elseif ($repliedFilter === 'no') {
            $query->whereNull('replied_at');
        }

        $entries = $query->latest()->paginate(12)->withQueryString();

        return view('office.feedback.index', [
            'office' => $office,
            'entries' => $entries,
            'repliedFilter' => $repliedFilter,
        ]);
    }

    public function edit(GovernmentOffice $office, Feedback $feedback): View
    {
        $this->assertFeedbackBelongsToOffice($office, $feedback);

        $feedback->load(['citizen:id,name,email', 'serviceRequest:id']);

        return view('office.feedback.edit', [
            'office' => $office,
            'feedback' => $feedback,
        ]);
    }

    public function updateReply(Request $request, GovernmentOffice $office, Feedback $feedback): RedirectResponse
    {
        $this->assertFeedbackBelongsToOffice($office, $feedback);

        $validated = $request->validate([
            'office_reply' => ['required', 'string', 'max:5000'],
        ]);

        $feedback->update([
            'office_reply' => $validated['office_reply'],
            'reply_is_public' => $request->boolean('reply_is_public'),
            'replied_at' => now(),
        ]);

        return redirect()
            ->route('office.feedback.index', $office)
            ->with('success', 'Your reply has been saved.');
    }

    private function assertFeedbackBelongsToOffice(GovernmentOffice $office, Feedback $feedback): void
    {
        abort_unless((int) $feedback->government_office_id === (int) $office->id, 404);
    }
}
