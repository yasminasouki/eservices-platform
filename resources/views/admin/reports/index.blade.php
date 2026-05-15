@extends('layouts.admin')

@section('title', __('ui.admin_reports_title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ __('ui.admin_reports_title') }}</h2>
            <p class="text-muted mb-0">{{ __('ui.admin_reports_subtitle') }}</p>
        </div>
    </div>

    <div class="card card-soft mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="government_office_id" class="form-label small text-muted mb-1">{{ __('ui.admin_reports_filter_office') }}</label>
                    <select name="government_office_id" id="government_office_id" class="form-select">
                        <option value="">{{ __('ui.admin_reports_filter_all_offices') }}</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" @selected((string) ($filters['government_office_id'] ?? '') === (string) $office->id)>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_from" class="form-label small text-muted mb-1">{{ __('ui.admin_reports_filter_from') }}</label>
                    <input type="date" name="date_from" id="date_from" class="form-control"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_to" class="form-label small text-muted mb-1">{{ __('ui.admin_reports_filter_to') }}</label>
                    <input type="date" name="date_to" id="date_to" class="form-control"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-8 col-lg-5 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>{{ __('ui.admin_reports_filter_apply') }}
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">{{ __('ui.admin_reports_filter_reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4">
            <div class="card card-soft p-3 h-100">
                <div class="text-muted small">{{ __('ui.admin_reports_stat_requests') }}</div>
                <div class="fs-3 fw-bold">{{ number_format($totalRequests) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-soft p-3 h-100">
                <div class="text-muted small">{{ __('ui.admin_reports_stat_revenue') }}</div>
                <div class="fs-3 fw-bold text-success">${{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-soft p-3 h-100 d-flex align-items-center">
                <p class="small text-muted mb-0">Revenue uses payments with status <strong>completed</strong>, dated by <strong>paid_at</strong> or <strong>created_at</strong>.</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card card-soft p-3 h-100">
                <h6 class="fw-semibold mb-3">{{ __('ui.admin_reports_chart_requests') }}</h6>
                <div style="height: 280px;">
                    <canvas id="chartRequests"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-soft p-3 h-100">
                <h6 class="fw-semibold mb-3">{{ __('ui.admin_reports_chart_revenue') }}</h6>
                <div style="height: 280px;">
                    <canvas id="chartRevenue"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0 fw-semibold">{{ __('ui.admin_reports_breakdown') }}</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('ui.admin_reports_col_office') }}</th>
                        <th class="text-end">{{ __('ui.admin_reports_col_requests') }}</th>
                        <th class="text-end">{{ __('ui.admin_reports_col_revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['name'] }}</td>
                            <td class="text-end">{{ number_format($row['request_count']) }}</td>
                            <td class="text-end">${{ number_format($row['revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">{{ __('ui.admin_reports_empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <th>{{ __('ui.admin_reports_col_total') }}</th>
                            <th class="text-end">{{ number_format($totalRequests) }}</th>
                            <th class="text-end">${{ number_format($totalRevenue, 2) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const labels = @json($chartLabels);
        const palette = ['#6b8cba','#7a9bc8','#5c7dab','#8aaad0','#4e6f9e','#7896c4','#6480b2','#8eb2d6'];

        function colors(n) {
            return Array.from({ length: n }, (_, i) => palette[i % palette.length]);
        }

        if (labels.length > 0) {
            new Chart(document.getElementById('chartRequests'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Requests',
                        data: @json($chartRequests),
                        backgroundColor: colors(labels.length),
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });

            new Chart(document.getElementById('chartRevenue'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue (USD)',
                        data: @json($chartRevenue),
                        backgroundColor: colors(labels.length),
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    </script>
@endpush
