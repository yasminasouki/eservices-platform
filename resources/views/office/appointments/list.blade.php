@extends('layouts.office')

@section('title', 'Appointments — ' . $office->name)

@section('content')
<div class="container py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mb-4">
        <h4 class="fw-bold mb-0">
            <i class="bi bi-calendar-check me-2" style="color:#2e7d32"></i>Appointments
        </h4>
        <p class="text-muted small mb-0">{{ $office->name }}</p>
    </div>

    <div class="card card-soft" id="office-appointments-list-root" data-appointments-live data-office-id="{{ $office->id }}">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Citizen</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Service Request</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td class="ps-4 fw-semibold">
                                    {{ $appointment->citizen->name ?? '—' }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->date)->format('d M Y') }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                                </td>
                                <td>
                                    @if($appointment->serviceRequest)
                                        <a href="{{ route('office.requests.show', [$office, $appointment->serviceRequest]) }}"
                                           class="text-decoration-none small">
                                            <i class="bi bi-file-earmark-text me-1"></i>#{{ $appointment->serviceRequest->id }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badgeMap = [
                                            'scheduled' => 'bg-primary',
                                            'confirmed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            'completed' => 'bg-secondary',
                                        ];
                                        $badgeClass = $badgeMap[$appointment->status] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    @if($appointment->status === 'scheduled')
                                        <div class="d-flex gap-2 justify-content-end">
                                            {{-- Confirm --}}
                                            <form method="POST"
                                                  action="{{ route('office.appointments.confirm', [$office, $appointment]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-lg me-1"></i>Confirm
                                                </button>
                                            </form>

                                            {{-- Cancel (opens modal) --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelModal"
                                                    data-action="{{ route('office.appointments.cancel', [$office, $appointment]) }}">
                                                <i class="bi bi-x-lg me-1"></i>Cancel
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x me-2"></i>No appointments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($appointments->hasPages())
        <div class="mt-3 d-flex justify-content-center">
            {{ $appointments->links() }}
        </div>
    @endif

</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="cancelForm">
            @csrf
            @method('PATCH')
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="cancelModalLabel">
                        <i class="bi bi-x-circle me-2 text-danger"></i>Cancel Appointment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="cancellation_reason" class="form-label fw-semibold">
                        Reason for Cancellation <span class="text-danger">*</span>
                    </label>
                    <textarea id="cancellation_reason"
                              name="cancellation_reason"
                              class="form-control"
                              rows="4"
                              maxlength="500"
                              placeholder="Please provide a reason..."
                              required></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i>Cancel Appointment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var officeId = {{ (int) $office->id }};
        var refreshInFlight = false;

        function refreshAppointmentsSection() {
            if (refreshInFlight) return;
            refreshInFlight = true;

            var url = new URL(window.location.href);
            url.searchParams.set('_appointments_refresh', Date.now().toString());

            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
                .then(function (response) { return response.text(); })
                .then(function (html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var incoming = doc.getElementById('office-appointments-list-root');
                    var current = document.getElementById('office-appointments-list-root');
                    if (!incoming || !current) return;
                    current.replaceWith(incoming);
                })
                .finally(function () {
                    refreshInFlight = false;
                });
        }

        window.addEventListener('appointments-updated', function (event) {
            var payload = event.detail || {};
            if (Number(payload.office_id) !== officeId) {
                return;
            }
            refreshAppointmentsSection();
        });

        setInterval(function () {
            if (document.visibilityState === 'visible') {
                refreshAppointmentsSection();
            }
        }, 5000);
    })();

    const cancelModal = document.getElementById('cancelModal');
    cancelModal.addEventListener('show.bs.modal', function (event) {
        const action = event.relatedTarget.dataset.action;
        document.getElementById('cancelForm').action = action;
        document.getElementById('cancellation_reason').value = '';
    });
</script>
@endpush

@endsection
