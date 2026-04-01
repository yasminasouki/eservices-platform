<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\GovernmentOffice;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Notifications\NewServiceRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CitizenServiceRequestController extends Controller
{
    public function create(GovernmentOffice $office, Service $service): View
    {
        $this->assertCatalogAccess($office, $service);

        return view('citizen.requests.create', [
            'office' => $office,
            'service' => $service,
        ]);
    }

    public function store(Request $request, GovernmentOffice $office, Service $service): RedirectResponse
    {
        $this->assertCatalogAccess($office, $service);

        $requiredCount = count($service->required_documents ?? []);

        $attachmentRules = ['array', 'max:15'];
        array_unshift($attachmentRules, $requiredCount > 0 ? 'required' : 'nullable');
        if ($requiredCount > 0) {
            $attachmentRules[] = 'size:'.$requiredCount;
        }

        $attachmentItemRules = ['file', 'max:12288'];
        if ($requiredCount > 0) {
            $attachmentItemRules = ['required', 'file', 'max:12288'];
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
            'attachments' => $attachmentRules,
            'attachments.*' => $attachmentItemRules,
        ]);

        $user = $request->user();

        $qrCode = $this->makeUniqueQrCode();

        $created = null;

        DB::transaction(function () use ($office, $service, $user, $validated, $request, $qrCode, &$created) {
            $created = ServiceRequest::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'government_office_id' => $office->id,
                'status' => 'pending',
                'qr_code' => $qrCode,
                'notes' => $validated['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            $docLabels = $service->required_documents ?? [];

            foreach ($request->file('attachments', []) as $index => $file) {
                if (! $file?->isValid()) {
                    continue;
                }

                $path = $file->store('service-requests/'.$created->id, 'public');

                Document::create([
                    'service_request_id' => $created->id,
                    'user_id' => $user->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'description' => $docLabels[$index] ?? null,
                    'type' => 'uploaded',
                    'uploaded_by' => 'citizen',
                ]);
            }
        });

        $office->staff()->each(function ($staffMember) use ($created, $service, $office) {
            $staffMember->notify(new NewServiceRequestNotification(
                auth()->user()->name,
                $service->name,
                $created->id,
                $office->id,
            ));
        });

        return redirect()
            ->route('citizen.requests.show', $created)
            ->with('success', 'Your request has been submitted. The office will review it shortly.');
    }

    public function index(): View
    {
        $requests = ServiceRequest::query()
            ->where('user_id', auth()->id())
            ->with(['service:id,name', 'governmentOffice:id,name', 'feedback:id,service_request_id'])
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('citizen.requests.index', compact('requests'));
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        abort_unless((int) $serviceRequest->user_id === (int) auth()->id(), 404);

        $serviceRequest->load([
            'service.category',
            'governmentOffice:id,name,address',
            'documents' => fn ($q) => $q->orderByDesc('id'),
            'feedback',
        ]);

        return view('citizen.requests.show', ['request' => $serviceRequest]);
    }

    private function assertCatalogAccess(GovernmentOffice $office, Service $service): void
    {
        abort_unless($office->is_active, 404);
        abort_unless($service->is_active, 404);
        abort_unless((int) $service->government_office_id === (int) $office->id, 404);
    }

    private function makeUniqueQrCode(): string
    {
        do {
            $code = 'SR-'.Str::upper(Str::replace('-', '', (string) Str::uuid()));
        } while (ServiceRequest::where('qr_code', $code)->exists());

        return $code;
    }
}
