<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeServiceCategoryController extends Controller
{
    public function index(GovernmentOffice $office): View
    {
        $categories = $office->serviceCategories()
            ->withCount('services')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('office.categories.index', compact('office', 'categories'));
    }

    public function create(GovernmentOffice $office): View
    {
        return view('office.categories.form', [
            'office'     => $office,
            'category'   => new ServiceCategory(['government_office_id' => $office->id]),
            'formAction' => route('office.categories.store', $office),
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request, GovernmentOffice $office): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $office->serviceCategories()->create($validated);

        return redirect()
            ->route('office.categories.index', $office)
            ->with('success', 'Category created successfully.');
    }

    public function edit(GovernmentOffice $office, ServiceCategory $category): View
    {
        $this->assertCategoryBelongsToOffice($office, $category);

        return view('office.categories.form', [
            'office'     => $office,
            'category'   => $category,
            'formAction' => route('office.categories.update', [$office, $category]),
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, GovernmentOffice $office, ServiceCategory $category): RedirectResponse
    {
        $this->assertCategoryBelongsToOffice($office, $category);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $category->update($validated);

        return redirect()
            ->route('office.categories.index', $office)
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(GovernmentOffice $office, ServiceCategory $category): RedirectResponse
    {
        $this->assertCategoryBelongsToOffice($office, $category);

        $category->delete();

        return redirect()
            ->route('office.categories.index', $office)
            ->with('success', 'Category deleted. Its services were removed with it.');
    }

    private function assertCategoryBelongsToOffice(GovernmentOffice $office, ServiceCategory $category): void
    {
        abort_unless((int) $category->government_office_id === (int) $office->id, 404);
    }
}
