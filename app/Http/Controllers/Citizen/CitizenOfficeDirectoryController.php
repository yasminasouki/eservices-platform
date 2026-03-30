<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
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

        return view('citizen.offices.show', compact('office', 'categories'));
    }
}
