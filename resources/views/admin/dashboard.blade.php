@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Admin Dashboard</h2>
            <p class="text-muted mb-0">System overview, live request activity, and top offices by volume and revenue.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-primary">
                <i class="bi bi-graph-up-arrow me-1"></i>Reports &amp; analytics
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="text-muted small">Government Offices</div>
                <div class="fs-3 fw-bold">{{ $stats['offices'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="text-muted small">Municipality Users</div>
                <div class="fs-3 fw-bold">{{ $stats['municipality_users'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="text-muted small">Citizens</div>
                <div class="fs-3 fw-bold">{{ $stats['citizens'] }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card card-soft p-3">
                <div class="text-muted small">Service Requests</div>
                <div class="fs-3 fw-bold">{{ $stats['requests'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card card-soft p-3 h-100">
                <h6 class="fw-semibold mb-3">Total revenue (completed payments)</h6>
                <div class="fs-3 fw-bold text-success">${{ number_format($stats['revenue'], 2) }}</div>
                <p class="text-muted small mb-0">All-time sum where <code class="small">payments.status</code> is completed.</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-soft p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold mb-0">Top offices by requests</h6>
                    <span class="badge bg-light text-dark">Top 10</span>
                </div>
                @if(count($volumeLabels ?? []) > 0)
                    <div style="height: 220px;">
                        <canvas id="chartDashboardVolume"></canvas>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-4 text-center">No request data yet.</p>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-soft p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-semibold mb-0">Top offices by revenue</h6>
                    <span class="badge bg-light text-dark">Top 10</span>
                </div>
                @if(count($revenueLabels ?? []) > 0)
                    <div style="height: 220px;">
                        <canvas id="chartDashboardRevenue"></canvas>
                    </div>
                @else
                    <p class="text-muted small mb-0 py-4 text-center">No completed payments yet.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0 fw-semibold">Latest Incoming Requests</div>
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Citizen</th>
                        <th>Office</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestRequests as $request)
                        <tr>
                            <td>#{{ $request->id }}</td>
                            <td>{{ $request->citizen?->name ?? 'N/A' }}</td>
                            <td>{{ $request->governmentOffice?->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ str_replace('_', ' ', $request->status) }}</span>
                            </td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @if((count($volumeLabels ?? []) > 0) || (count($revenueLabels ?? []) > 0))
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const palette = ['#1a237e','#1565c0','#00838f','#6a1b9a','#c62828','#ef6c00','#2e7d32','#455a64'];
        function colors(n) {
            return Array.from({ length: n }, (_, i) => palette[i % palette.length]);
        }
        const volLabels = @json($volumeLabels);
        const volData = @json($volumeCounts);
        const revLabels = @json($revenueLabels);
        const revData = @json($revenueAmounts);

        if (volLabels.length > 0) {
            new Chart(document.getElementById('chartDashboardVolume'), {
                type: 'bar',
                data: {
                    labels: volLabels,
                    datasets: [{
                        label: 'Requests',
                        data: volData,
                        backgroundColor: colors(volLabels.length),
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
        if (revLabels.length > 0) {
            new Chart(document.getElementById('chartDashboardRevenue'), {
                type: 'bar',
                data: {
                    labels: revLabels,
                    datasets: [{
                        label: 'Revenue (USD)',
                        data: revData,
                        backgroundColor: colors(revLabels.length),
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true }
                    }
                }
            });
        }
    </script>
    @endif
@endpush
