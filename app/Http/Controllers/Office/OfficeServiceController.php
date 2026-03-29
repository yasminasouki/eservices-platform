<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfficeServiceController extends Controller
{
    public function index(GovernmentOffice $office): View
    {
        $services = $office->services()
            ->with('category:id,name')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('office.services.index', compact('office', 'services'));
    }

    public function create(GovernmentOffice $office): View
    {
        $categories = $office->serviceCategories()->orderBy('name')->get(['id', 'name']);

        abort_if($categories->isEmpty(), 403, 'Create at least one service category before adding services.');

        return view('office.services.form', [
            'office'     => $office,
            'service'    => new Service([
                'government_office_id' => $office->id,
                'duration_unit'        => 'days',
                'is_active'            => true,
            ]),
            'categories' => $categories,
            'formAction' => route('office.services.store', $office),
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request, GovernmentOffice $office): RedirectResponse
    {
        $categoryIds = $office->serviceCategories()->pluck('id')->all();

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:5000'],
            'price'               => ['required', 'numeric', 'min:0'],
            'duration'            => ['nullable', 'integer', 'min:1'],
            'duration_unit'       => ['required', Rule::in(['minutes', 'hours', 'days'])],
            'required_documents'  => ['nullable', 'string', 'max:5000'],
            'service_category_id' => ['required', 'integer', Rule::in($categoryIds)],
            'is_active'           => ['sometimes', 'boolean'],
        ]);

        $docs = $this->parseDocumentLines($validated['required_documents'] ?? null);

        $office->services()->create([
            'name'                => $validated['name'],
            'description'         => $validated['description'] ?? null,
            'price'               => $validated['price'],
            'duration'            => $validated['duration'] ?? null,
            'duration_unit'       => $validated['duration_unit'],
            'required_documents'  => $docs ?: null,
            'service_category_id' => (int) $validated['service_category_id'],
            'is_active'           => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('office.services.index', $office)
            ->with('success', 'Service created successfully.');
    }

    public function edit(GovernmentOffice $office, Service $service): View
    {
        $this->assertServiceBelongsToOffice($office, $service);

        $categories = $office->serviceCategories()->orderBy('name')->get(['id', 'name']);

        return view('office.services.form', [
            'office'     => $office,
            'service'    => $service,
            'categories' => $categories,
            'formAction' => route('office.services.update', [$office, $service]),
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, GovernmentOffice $office, Service $service): RedirectResponse
    {
        $this->assertServiceBelongsToOffice($office, $service);

        $categoryIds = $office->serviceCategories()->pluck('id')->all();

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:5000'],
            'price'               => ['required', 'numeric', 'min:0'],
            'duration'            => ['nullable', 'integer', 'min:1'],
            'duration_unit'       => ['required', Rule::in(['minutes', 'hours', 'days'])],
            'required_documents'  => ['nullable', 'string', 'max:5000'],
            'service_category_id' => ['required', 'integer', Rule::in($categoryIds)],
            'is_active'           => ['sometimes', 'boolean'],
        ]);

        $docs = $this->parseDocumentLines($validated['required_documents'] ?? null);

        $service->update([
            'name'                => $validated['name'],
            'description'         => $validated['description'] ?? null,
            'price'               => $validated['price'],
            'duration'            => $validated['duration'] ?? null,
            'duration_unit'       => $validated['duration_unit'],
            'required_documents'  => $docs ?: null,
            'service_category_id' => (int) $validated['service_category_id'],
            'is_active'           => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('office.services.index', $office)
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(GovernmentOffice $office, Service $service): RedirectResponse
    {
        $this->assertServiceBelongsToOffice($office, $service);

        $service->delete();

        return redirect()
            ->route('office.services.index', $office)
            ->with('success', 'Service deleted successfully.');
    }

    private function assertServiceBelongsToOffice(GovernmentOffice $office, Service $service): void
    {
        abort_unless((int) $service->government_office_id === (int) $office->id, 404);
    }

    private function parseDocumentLines(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', $raw) ?: []
        )));
    }
}
