<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OfficeContextController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_id' => ['required', 'integer'],
        ]);

        $id = (int) $validated['office_id'];

        if (! $request->user()->governmentOffices()->whereKey($id)->exists()) {
            abort(403);
        }

        $request->session()->put('office_context_id', $id);

        return back();
    }
}
