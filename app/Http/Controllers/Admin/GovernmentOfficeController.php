<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\Municipality;
use Illuminate\Http\Request;

class GovernmentOfficeController extends Controller
{
    public function index()
    {
        $offices = GovernmentOffice::query()
            ->with('municipality:id,name')
            ->latest()
            ->paginate(12);

        return view('admin.offices.index', compact('offices'));
    }

    public function create()
    {
        $municipalities = Municipality::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.offices.form', [
            'office' => new GovernmentOffice(),
            'municipalities' => $municipalities,
            'formAction' => route('admin.offices.store'),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['is_active'] = $request->boolean('is_active');

        GovernmentOffice::create($validated);

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Government office created successfully.');
    }

    public function edit(GovernmentOffice $office)
    {
        $municipalities = Municipality::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.offices.form', [
            'office' => $office,
            'municipalities' => $municipalities,
            'formAction' => route('admin.offices.update', $office),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, GovernmentOffice $office)
    {
        $validated = $this->validateRequest($request);
        $validated['is_active'] = $request->boolean('is_active');

        $office->update($validated);

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Government office updated successfully.');
    }

    public function destroy(GovernmentOffice $office)
    {
        $office->delete();

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Government office deleted successfully.');
    }

    private function validateRequest(Request $request): array
    {
        $this->normalizeOptionalInputs($request);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'google_maps_url' => ['nullable', 'url', 'max:500'],
            'municipality_id' => ['nullable', 'exists:municipalities,id'],
        ]);
    }

    private function normalizeOptionalInputs(Request $request): void
    {
        foreach (['email', 'phone', 'website', 'google_maps_url', 'municipality_id'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }
    }
}
