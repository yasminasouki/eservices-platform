<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\GovernmentOffice;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestStatusLog;
use App\Notifications\OfficeAddedDocumentNotification;
use App\Services\ServiceRequestPdfAutomationService;
use App\Support\QrCodeDataUri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OfficeServiceRequestController extends Controller
{
    public function index(Request $request, GovernmentOffice $office): View
    {
        $this->normalizeFilterInputs($request);

        $validated = $request->validate([
            'status' => ['nullable', 'string', Rule::in(ServiceRequest::STATUSES)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        if (
            ! empty($validated['date_from'])
            && ! empty($validated['date_to'])
            && $validated['date_to'] < $validated['date_from']
        ) {
            throw ValidationException::withMessages([
                'date_to' => 'The end date must be on or after the start date.',
            ]);
        }

        $filterQuery = ServiceRequest::query()->where('government_office_id', $office->id);

        if (! empty($validated['date_from'])) {
            $filterQuery->whereRaw('DATE(COALESCE(submitted_at, created_at)) >= ?', [$validated['date_from']]);
        }
        if (! empty($validated['date_to'])) {
            $filterQuery->whereRaw('DATE(COALESCE(submitted_at, created_at)) <= ?', [$validated['date_to']]);
        }

        $search = trim((string) ($validated['q'] ?? ''));
        if ($search !== '') {
            $filterQuery->whereHas('citizen', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $statusCountsRaw = (clone $filterQuery)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusCounts = collect(ServiceRequest::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($statusCountsRaw[$status] ?? 0)]);

        $listQuery = clone $filterQuery;
        if (! empty($validated['status'])) {
            $listQuery->where('status', $validated['status']);
        }

        $requests = $listQuery
            ->with(['citizen:id,name,email', 'service:id,name'])
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('office.requests.index', [
            'office' => $office,
            'requests' => $requests,
            'statusCounts' => $statusCounts,
            'filters' => [
                'status' => $validated['status'] ?? null,
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
                'q' => $search !== '' ? $search : null,
            ],
        ]);
    }

    public function show(GovernmentOffice $office, ServiceRequest $serviceRequest): View
    {
        $this->assertRequestBelongsToOffice($office, $serviceRequest);

        $serviceRequest->load([
            'citizen:id,name,email,phone',
            'service.category',
            'governmentOffice:id,name',
            'documents.uploader:id,name',
            'statusLogs' => fn ($q) => $q->orderByDesc('created_at')->with('changedBy:id,name'),
        ]);

        $trackingUrl = route('requests.track', ['token' => $serviceRequest->qr_code]);
        $trackingQrDataUri = QrCodeDataUri::svgDataUri($trackingUrl, 260);

        return view('office.requests.show', [
            'office' => $office,
            'request' => $serviceRequest,
            'trackingUrl' => $trackingUrl,
            'trackingQrDataUri' => $trackingQrDataUri,
        ]);
    }

    public function updateStatus(Request $request, GovernmentOffice $office, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->assertRequestBelongsToOffice($office, $serviceRequest);

        foreach (['rejection_reason', 'missing_docs_note', 'status_note'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(ServiceRequest::STATUSES)],
            'rejection_reason' => ['nullable', 'string', 'max:5000'],
            'missing_docs_note' => ['nullable', 'string', 'max:5000'],
            'status_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['status'] === 'rejected' && empty($validated['rejection_reason'])) {
            throw ValidationException::withMessages([
                'rejection_reason' => 'A rejection reason is required when rejecting a request.',
            ]);
        }

        if ($validated['status'] === 'missing_documents' && empty($validated['missing_docs_note'])) {
            throw ValidationException::withMessages([
                'missing_docs_note' => 'Please describe which documents are missing.',
            ]);
        }

        $oldStatus = $serviceRequest->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use ($serviceRequest, $oldStatus, $newStatus, $validated) {
            $updates = [
                'status' => $newStatus,
            ];

            if ($newStatus === 'rejected') {
                $updates['rejection_reason'] = $validated['rejection_reason'];
            } else {
                $updates['rejection_reason'] = null;
            }

            if ($newStatus === 'missing_documents') {
                $updates['missing_docs_note'] = $validated['missing_docs_note'];
            } else {
                $updates['missing_docs_note'] = null;
            }

            if (! $serviceRequest->reviewed_at && $newStatus !== 'pending') {
                $updates['reviewed_at'] = now();
            }

            if ($newStatus === 'completed') {
                $updates['completed_at'] = now();
            } else {
                $updates['completed_at'] = null;
            }

            $serviceRequest->update($updates);

            if ($oldStatus !== $newStatus) {
                ServiceRequestStatusLog::create([
                    'service_request_id' => $serviceRequest->id,
                    'changed_by' => auth()->id(),
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'notes' => $validated['status_note'] ?? null,
                    'created_at' => now(),
                ]);
            } elseif (($validated['status_note'] ?? '') !== '') {
                ServiceRequestStatusLog::create([
                    'service_request_id' => $serviceRequest->id,
                    'changed_by' => auth()->id(),
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'notes' => $validated['status_note'],
                    'created_at' => now(),
                ]);
            }
        });

        $success = 'Request status updated.';
        if ($oldStatus !== $newStatus) {
            $serviceRequest->refresh();
            app(ServiceRequestPdfAutomationService::class)->onStatusChanged(
                $serviceRequest,
                $oldStatus,
                $newStatus,
                auth()->id(),
            );
            if ($newStatus === 'approved') {
                $success .= ' An official approval PDF was added under Documents.';
            } elseif ($newStatus === 'completed') {
                $success .= ' Completion certificate and receipt PDFs were added under Documents.';
            }
        }

        return redirect()
            ->route('office.requests.show', [$office, $serviceRequest])
            ->with('success', $success);
    }

    public function storeDocument(Request $request, GovernmentOffice $office, ServiceRequest $serviceRequest): RedirectResponse
    {
        $this->assertRequestBelongsToOffice($office, $serviceRequest);

        if ($request->input('description') === '') {
            $request->merge(['description' => null]);
        }

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:12288'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', Rule::in(['certificate', 'generated', 'receipt'])],
        ]);

        $file = $request->file('file');
        $path = $file->store('service-requests/'.$serviceRequest->id, 'public');

        Document::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => auth()->id(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'uploaded_by' => 'office',
        ]);

        $serviceRequest->loadMissing('citizen');
        if ($serviceRequest->citizen) {
            $serviceRequest->citizen->notify(new OfficeAddedDocumentNotification($serviceRequest, $office));
        }

        return redirect()
            ->route('office.requests.show', [$office, $serviceRequest])
            ->with('success', 'Document uploaded successfully.');
    }

    private function assertRequestBelongsToOffice(GovernmentOffice $office, ServiceRequest $serviceRequest): void
    {
        abort_unless((int) $serviceRequest->government_office_id === (int) $office->id, 404);
    }

    private function normalizeFilterInputs(Request $request): void
    {
        foreach (['status', 'date_from', 'date_to', 'q'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }
    }
}
