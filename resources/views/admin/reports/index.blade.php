@extends('layouts.admin')

@section('title', 'Reports & analytics')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Reports & analytics</h2>
            <p class="text-muted mb-0">Request volume and revenue by government office. Filters apply to submission dates for requests and payment dates for revenue.</p>
        </div>
    </div>

    <div class="card card-soft mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="government_office_id" class="form-label small text-muted mb-1">Office</label>
                    <select name="government_office_id" id="government_office_id" class="form-select">
                        <option value="">All offices</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" @selected((string) ($filters['government_office_id'] ?? '') === (string) $office->id)>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_from" class="form-label small text-muted mb-1">From (requests / payments)</label>
                    <input type="date" name="date_from" id="date_from" class="form-control"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label for="date_to" class="form-label small text-muted mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-8 col-lg-5 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i>Apply
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-4">
            <div class="card card-soft p-3 h-100">
                <div class="text-muted small">Total requests (filtered)</div>
                <div class="fs-3 fw-bold">{{ number_format($totalRequests) }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="card card-soft p-3 h-100">
                <div class="text-muted small">Total revenue (completed)</div>
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
                <h6 class="fw-semibold mb-3">Requests per office</h6>
                <div style="height: 280px;">
                    <canvas id="chartRequests"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-soft p-3 h-100">
                <h6 class="fw-semibold mb-3">Revenue per office (USD)</h6>
                <div style="height: 280px;">
                    <canvas id="chartRevenue"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0 fw-semibold">Breakdown by office</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Office</th>
                        <th class="text-end">Requests</th>
                        <th class="text-end">Revenue</th>
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
                            <td colspan="3" class="text-center text-muted py-4">No offices match the filter.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
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
        const palette = ['#1a237e','#1565c0','#00838f','#6a1b9a','#c62828','#ef6c00','#2e7d32','#455a64'];

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
