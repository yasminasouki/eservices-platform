<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\GovernmentOffice;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Notifications\NewDocumentUploadedNotification;
use App\Notifications\NewServiceRequestNotification;
use App\Support\QrCodeDataUri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $requiresPayment = (float) $service->price > 0;

        $created = null;

        DB::transaction(function () use ($office, $service, $user, $validated, $request, $qrCode, $requiresPayment, &$created) {
            $created = ServiceRequest::create([
                'user_id' => $user->id,
                'service_id' => $service->id,
                'government_office_id' => $office->id,
                'status' => 'pending',
                'qr_code' => $qrCode,
                'notes' => $validated['notes'] ?? null,
                'submitted_at' => $requiresPayment ? null : now(),
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

            if ($requiresPayment) {
                Payment::create([
                    'service_request_id' => $created->id,
                    'user_id' => $user->id,
                    'amount' => $service->price,
                    'currency' => 'USD',
                    'method' => 'pending',
                    'status' => 'pending',
                ]);
            }
        });

        if (! $requiresPayment) {
            $office->staff()->each(function ($staffMember) use ($created, $service, $office) {
                $staffMember->notify(new NewServiceRequestNotification(
                    auth()->user()->name,
                    $service->name,
                    $created->id,
                    $office->id,
                ));
            });
        }

        if ($requiresPayment) {
            return redirect()
                ->route('citizen.requests.pay', $created)
                ->with('success', 'Application saved. Complete payment to submit your request to the office.');
        }

        return redirect()
            ->route('citizen.requests.show', $created)
            ->with('success', 'Your request has been submitted. Use the QR code on this page to track status offline without logging in.');
    }

    public function index(): View
    {
        $requests = ServiceRequest::query()
            ->where('user_id', auth()->id())
            ->with(['service:id,name,price', 'governmentOffice:id,name', 'feedback:id,service_request_id', 'payment:id,service_request_id,status'])
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
            'payment',
            'documents' => fn ($q) => $q->orderByDesc('id'),
            'feedback',
        ]);

        $trackingUrl = route('requests.track', ['token' => $serviceRequest->qr_code]);
        $trackingQrDataUri = QrCodeDataUri::svgDataUri($trackingUrl, 260);

        $canUploadFollowupDocuments = $serviceRequest->submitted_at !== null
            && in_array(
                $serviceRequest->status,
                ServiceRequest::STATUSES_ALLOWING_CITIZEN_FOLLOWUP_DOCUMENTS,
                true
            );

        return view('citizen.requests.show', [
            'request' => $serviceRequest,
            'trackingUrl' => $trackingUrl,
            'trackingQrDataUri' => $trackingQrDataUri,
            'canUploadFollowupDocuments' => $canUploadFollowupDocuments,
            'awaitingPayment' => $serviceRequest->awaitingCitizenPayment(),
        ]);
    }

    public function storeAdditionalDocuments(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        abort_unless((int) $serviceRequest->user_id === (int) $request->user()->id, 404);

        if ($serviceRequest->awaitingCitizenPayment()) {
            abort(403, 'Complete payment before uploading additional documents.');
        }

        if (! in_array($serviceRequest->status, ServiceRequest::STATUSES_ALLOWING_CITIZEN_FOLLOWUP_DOCUMENTS, true)) {
            abort(403, 'You cannot upload documents for this request in its current status.');
        }

        if ($request->input('note') === '') {
            $request->merge(['note' => null]);
        }

        $validated = $request->validate([
            'attachments' => ['required', 'array', 'min:1', 'max:15'],
            'attachments.*' => ['file', 'max:12288'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $storedCount = 0;

        $note = $validated['note'] ?? null;

        DB::transaction(function () use ($serviceRequest, $user, $request, $note, &$storedCount) {
            foreach ($request->file('attachments', []) as $file) {
                if (! $file?->isValid()) {
                    continue;
                }

                $path = $file->store('service-requests/'.$serviceRequest->id, 'public');

                Document::create([
                    'service_request_id' => $serviceRequest->id,
                    'user_id' => $user->id,
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'description' => $note,
                    'type' => 'uploaded',
                    'uploaded_by' => 'citizen',
                ]);

                $storedCount++;
            }
        });

        if ($storedCount === 0) {
            return back()
                ->withInput()
                ->withErrors(['attachments' => 'No valid files were uploaded.']);
        }

        $office = $serviceRequest->governmentOffice;
        if ($office) {
            $office->staff()->each(function ($staffMember) use ($user, $serviceRequest, $office, $storedCount) {
                $staffMember->notify(new NewDocumentUploadedNotification(
                    $user->name,
                    $serviceRequest->id,
                    $office->id,
                    $storedCount,
                ));
            });
        }

        $msg = $storedCount === 1
            ? 'Your document was uploaded. The office has been notified.'
            : $storedCount.' documents were uploaded. The office has been notified.';

        return redirect()
            ->route('citizen.requests.show', $serviceRequest)
            ->with('success', $msg);
    }

    public function downloadDocument(ServiceRequest $serviceRequest, Document $document): mixed
    {
        abort_unless((int) $serviceRequest->user_id === (int) auth()->id(), 404);
        abort_unless((int) $document->service_request_id === (int) $serviceRequest->id, 404);
        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
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
