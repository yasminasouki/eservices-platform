<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\IdVerificationRequest;
use App\Services\IdVerificationService;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IdVerificationController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $verification = IdVerificationRequest::where('user_id', $user->id)
            ->latest()
            ->first();

        return view('citizen.id-verify', compact('user', 'verification'));
    }

    public function upload(Request $request, IdVerificationService $idService)
    {
        $request->validate([
            'id_document_front' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_document_back' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $frontPath = $request->file('id_document_front')->store('id_documents', 'local');
        $backPath = $request->file('id_document_back')->store('id_documents', 'local');

        try {
            $idService->submitForVerification($frontPath, $backPath);
        } catch (HttpClientException $e) {
            Storage::disk('local')->delete([$frontPath, $backPath]);
            report($e);

            return redirect()->route('citizen.id.verify')
                ->withErrors([
                    'id_document_front' => 'We could not reach the ID scanning service (network or DNS issue). Check your internet connection, try turning off VPN, or change DNS (e.g. 1.1.1.1), then try again.',
                ]);
        }

        Auth::user()->update(['id_document' => $frontPath]);

        return redirect()->route('citizen.id.verify')
            ->with('success', 'Your ID documents have been submitted and are awaiting review.');
    }

    public function save(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'father_name' => ['nullable', 'string'],
            'mother_name' => ['nullable', 'string'],
            'dob' => ['required', 'string'],
            'place_of_birth' => ['nullable', 'string'],
            'gender' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string'],
            'marital_status' => ['nullable', 'string'],
            'id_number' => ['required', 'string'],
            'registry_number' => ['nullable', 'string'],
            'locality' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'governorate' => ['nullable', 'string'],
            'issue_date' => ['nullable', 'string'],
            'expiry_date' => ['nullable', 'string'],
        ]);

        IdVerificationRequest::where('user_id', Auth::id())
            ->latest()
            ->firstOrFail()
            ->update([
                'extracted_name' => $request->first_name.' '.$request->last_name,
                'extracted_father_name' => $request->father_name,
                'extracted_mother_name' => $request->mother_name,
                'extracted_dob' => $request->dob,
                'extracted_place_of_birth' => $request->place_of_birth,
                'extracted_gender' => $request->gender,
                'extracted_blood_type' => $request->blood_type,
                'extracted_marital_status' => $request->marital_status,
                'extracted_id_number' => $request->id_number,
                'extracted_registry_number' => $request->registry_number,
                'extracted_locality' => $request->locality,
                'extracted_district' => $request->district,
                'extracted_governorate' => $request->governorate,
                'extracted_issue_date' => $request->issue_date ?: null,
                'extracted_expiry_date' => $request->expiry_date ?: null,
            ]);

        return redirect()->route('citizen.dashboard')
            ->with('success', 'Your information has been saved successfully.');
    }
}
