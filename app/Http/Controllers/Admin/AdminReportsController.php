<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminReportsController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeFilterInputs($request);

        $validated = $request->validate([
            'government_office_id' => ['nullable', 'integer', 'exists:government_offices,id'],
            'date_from'            => ['nullable', 'date'],
            'date_to'              => ['nullable', 'date'],
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

        $officesQuery = GovernmentOffice::query()->orderBy('name');
        if (! empty($validated['government_office_id'])) {
            $officesQuery->where('id', $validated['government_office_id']);
        }
        $offices = $officesQuery->get(['id', 'name']);

        $requestCounts = $this->requestCountByOfficeQuery($validated)
            ->pluck('request_count', 'government_office_id');

        $revenueSums = $this->revenueByOfficeQuery($validated)
            ->pluck('revenue_total', 'government_office_id');

        $rows = $offices->map(function (GovernmentOffice $office) use ($requestCounts, $revenueSums) {
            return [
                'id'             => $office->id,
                'name'           => $office->name,
                'request_count'  => (int) ($requestCounts[$office->id] ?? 0),
                'revenue'        => (float) ($revenueSums[$office->id] ?? 0),
            ];
        })->sortByDesc('request_count')->values();

        $totalRequests = (int) $rows->sum('request_count');
        $totalRevenue  = (float) $rows->sum('revenue');

        $officesForFilter = GovernmentOffice::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.reports.index', [
            'rows'            => $rows,
            'totalRequests'   => $totalRequests,
            'totalRevenue'    => $totalRevenue,
            'offices'         => $officesForFilter,
            'filters'         => [
                'government_office_id' => $validated['government_office_id'] ?? null,
                'date_from'            => $validated['date_from'] ?? null,
                'date_to'              => $validated['date_to'] ?? null,
            ],
            'chartLabels'     => $rows->pluck('name')->values()->all(),
            'chartRequests'   => $rows->pluck('request_count')->values()->all(),
            'chartRevenue'    => $rows->pluck('revenue')->values()->all(),
        ]);
    }

    private function normalizeFilterInputs(Request $request): void
    {
        foreach (['government_office_id', 'date_from', 'date_to'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }
    }

    /** @param  array<string, mixed>  $filters */
    private function requestCountByOfficeQuery(array $filters): Builder
    {
        $q = ServiceRequest::query()
            ->selectRaw('government_office_id, COUNT(*) as request_count')
            ->groupBy('government_office_id');

        if (! empty($filters['government_office_id'])) {
            $q->where('government_office_id', $filters['government_office_id']);
        }

        if (! empty($filters['date_from'])) {
            $q->whereRaw('DATE(COALESCE(submitted_at, created_at)) >= ?', [$filters['date_from']]);
        }

        if (! empty($filters['date_to'])) {
            $q->whereRaw('DATE(COALESCE(submitted_at, created_at)) <= ?', [$filters['date_to']]);
        }

        return $q;
    }

    /** @param  array<string, mixed>  $filters */
    private function revenueByOfficeQuery(array $filters)
    {
        $q = Payment::query()
            ->join('service_requests', 'service_requests.id', '=', 'payments.service_request_id')
            ->where('payments.status', 'completed')
            ->selectRaw('service_requests.government_office_id, SUM(payments.amount) as revenue_total')
            ->groupBy('service_requests.government_office_id');

        if (! empty($filters['government_office_id'])) {
            $q->where('service_requests.government_office_id', $filters['government_office_id']);
        }

        if (! empty($filters['date_from'])) {
            $q->whereRaw('DATE(COALESCE(payments.paid_at, payments.created_at)) >= ?', [$filters['date_from']]);
        }

        if (! empty($filters['date_to'])) {
            $q->whereRaw('DATE(COALESCE(payments.paid_at, payments.created_at)) <= ?', [$filters['date_to']]);
        }

        return $q;
    }
}
