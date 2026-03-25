<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\IdVerificationRequest;
use App\Services\IdVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdVerificationController extends Controller
{
    public function show()
    {
        $user         = Auth::user();
        $verification = IdVerificationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return view('citizen.id-verify', compact('user', 'verification'));
    }

    public function upload(Request $request, IdVerificationService $idService)
    {
        $request->validate([
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $idPath = $request->file('id_document')->store('id_documents', 'local');

        $idService->submitForVerification($idPath);

        Auth::user()->update(['id_document' => $idPath]);

        return redirect()->route('citizen.id.verify')
            ->with('success', 'Your ID document has been submitted and is awaiting review.');
    }

    public function save(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string'],
            'last_name'  => ['required', 'string'],
            'dob'        => ['required', 'string'],
            'id_number'  => ['required', 'string'],
        ]);

        IdVerificationRequest::where('user_id', Auth::id())
            ->latest()
            ->firstOrFail()
            ->update([
                'extracted_name'      => $request->first_name . ' ' . $request->last_name,
                'extracted_dob'       => $request->dob,
                'extracted_id_number' => $request->id_number,
            ]);

        return redirect()->route('citizen.dashboard')
            ->with('success', 'Your information has been saved successfully.');
    }
}
