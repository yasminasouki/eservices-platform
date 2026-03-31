<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\GovernmentOffice;
use Illuminate\View\View;

class CitizenOfficeDirectoryController extends Controller
{
    public function index(): View
    {
        $offices = GovernmentOffice::query()
            ->where('is_active', true)
            ->withCount(['services' => fn ($q) => $q->where('is_active', true)])
            ->with('municipality:id,name')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('citizen.offices.index', compact('offices'));
    }

    public function show(GovernmentOffice $office): View
    {
        abort_unless($office->is_active, 404);

        $categories = $office->serviceCategories()
            ->whereHas('services', fn ($q) => $q->where('is_active', true))
            ->with([
                'services' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        $avgRating = Feedback::query()
            ->where('government_office_id', $office->id)
            ->whereNotNull('rating')
            ->avg('rating');

        $publicReviews = Feedback::query()
            ->where('government_office_id', $office->id)
            ->whereNotNull('rating')
            ->latest()
            ->limit(12)
            ->get(['id', 'rating', 'comment', 'office_reply', 'reply_is_public', 'replied_at', 'created_at']);

        return view('citizen.offices.show', [
            'office' => $office,
            'categories' => $categories,
            'avgRating' => $avgRating !== null ? round((float) $avgRating, 1) : null,
            'publicReviews' => $publicReviews,
        ]);
    }
}
