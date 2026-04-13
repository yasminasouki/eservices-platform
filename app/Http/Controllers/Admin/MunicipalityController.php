<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    public function index()
    {
        $municipalities = Municipality::query()
            ->withCount('governmentOffices')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.municipalities.index', compact('municipalities'));
    }

    public function create()
    {
        return view('admin.municipalities.form', [
            'municipality' => new Municipality,
            'formAction' => route('admin.municipalities.store'),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        Municipality::create($validated);

        return redirect()
            ->route('admin.municipalities.index')
            ->with('success', 'Municipality created successfully.');
    }

    public function edit(Municipality $municipality)
    {
        return view('admin.municipalities.form', [
            'municipality' => $municipality,
            'formAction' => route('admin.municipalities.update', $municipality),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Municipality $municipality)
    {
        $validated = $this->validateRequest($request);

        $municipality->update($validated);

        return redirect()
            ->route('admin.municipalities.index')
            ->with('success', 'Municipality updated successfully.');
    }

    public function destroy(Municipality $municipality)
    {
        $municipality->delete();

        return redirect()
            ->route('admin.municipalities.index')
            ->with('success', 'Municipality deleted. Linked offices are now unassigned from this municipality.');
    }

    private function validateRequest(Request $request): array
    {
        if ($request->input('region') === '') {
            $request->merge(['region' => null]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
