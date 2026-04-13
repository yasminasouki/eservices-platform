<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\ServiceRequest;
use App\Support\QrCodeDataUri;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminServiceOperationsController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeFilterInputs($request);

        $validated = $request->validate([
            'government_office_id' => ['nullable', 'integer', 'exists:government_offices,id'],
            'status' => ['nullable', 'string', Rule::in(ServiceRequest::STATUSES)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
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

        $filterQuery = ServiceRequest::query();
        $this->applyOfficeAndDateFilters($filterQuery, $validated);

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
            ->with([
                'citizen:id,name,email',
                'governmentOffice:id,name',
                'service:id,name',
            ])
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $offices = GovernmentOffice::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.service-requests.index', [
            'requests' => $requests,
            'offices' => $offices,
            'statusCounts' => $statusCounts,
            'filters' => [
                'government_office_id' => $validated['government_office_id'] ?? null,
                'status' => $validated['status'] ?? null,
                'date_from' => $validated['date_from'] ?? null,
                'date_to' => $validated['date_to'] ?? null,
            ],
        ]);
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        $serviceRequest->load([
            'citizen:id,name,email,phone',
            'service.category',
            'governmentOffice.municipality:id,name',
            'payment',
            'documents.uploader:id,name',
            'statusLogs' => fn ($q) => $q->orderByDesc('created_at')->with('changedBy:id,name'),
        ]);

        $trackingUrl = route('requests.track', ['token' => $serviceRequest->qr_code]);
        $trackingQrDataUri = QrCodeDataUri::svgDataUri($trackingUrl, 220);

        return view('admin.service-requests.show', [
            'request' => $serviceRequest,
            'trackingUrl' => $trackingUrl,
            'trackingQrDataUri' => $trackingQrDataUri,
        ]);
    }

    private function normalizeFilterInputs(Request $request): void
    {
        foreach (['government_office_id', 'status', 'date_from', 'date_to'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }
    }

    /** @param  array<string, mixed>  $filters */
    private function applyOfficeAndDateFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['government_office_id'])) {
            $query->where('government_office_id', $filters['government_office_id']);
        }

        // Submission timeline: prefer submitted_at; fall back when not recorded yet.
        if (! empty($filters['date_from'])) {
            $query->whereRaw('DATE(COALESCE(submitted_at, created_at)) >= ?', [$filters['date_from']]);
        }

        if (! empty($filters['date_to'])) {
            $query->whereRaw('DATE(COALESCE(submitted_at, created_at)) <= ?', [$filters['date_to']]);
        }
    }
}
