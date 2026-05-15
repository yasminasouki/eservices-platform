@extends('layouts.office')

@section('title', 'Appointments — ' . $office->name)

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* Appointments card */
    .appts-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }

    .appts-table { width: 100%; border-collapse: collapse; }
    .appts-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .65rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .appts-table thead th:first-child { padding-left: 1.4rem; }
    .appts-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }
    .appts-table tbody tr { border-bottom: 1px solid #f0fdf4; transition: background .12s; }
    .appts-table tbody tr:last-child { border-bottom: none; }
    .appts-table tbody tr:hover { background: #f0fdf4; }
    .appts-table tbody td {
        padding: .85rem 1rem; vertical-align: middle; font-size: .875rem; color: #374151;
    }
    .appts-table tbody td:first-child { padding-left: 1.4rem; }
    .appts-table tbody td:last-child  { padding-right: 1.4rem; }

    /* Citizen name */
    .citizen-name { font-weight: 700; color: #0f2d13; }

    /* Status pills */
    .status-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 700;
        padding: .28rem .7rem; border-radius: 999px; white-space: nowrap;
    }
    .pill-scheduled { background: #dbeafe; color: #1e40af; }
    .pill-confirmed { background: #dcfce7; color: #14532d; }
    .pill-cancelled { background: #fde8e8; color: #991b1b; }
    .pill-completed { background: #f3f4f6; color: #374151; }

    /* Service request link */
    .req-link {
        color: #16a34a; text-decoration: none;
        font-size: .82rem; font-weight: 600;
        display: inline-flex; align-items: center; gap: .25rem;
    }
    .req-link:hover { color: #14532d; text-decoration: underline; }

    /* Action buttons */
    .act-group { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; }
    .btn-confirm {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 700; padding: .3rem .8rem;
        border-radius: 7px; border: none;
        background: #16a34a; color: #fff; cursor: pointer; white-space: nowrap;
        transition: background .12s;
    }
    .btn-confirm:hover { background: #15803d; }
    .btn-cancel-appt {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 7px; border: 1px solid #fecaca;
        background: #fef2f2; color: #991b1b; cursor: pointer; white-space: nowrap;
        transition: background .12s;
    }
    .btn-cancel-appt:hover { background: #fde8e8; }

    /* Empty state */
    .empty-state { text-align: center; padding: 3.5rem 2rem; }
    .empty-icon {
        width: 68px; height: 68px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #86efac; font-size: 1.7rem; margin: 0 auto 1rem;
    }
    .empty-state h6 { font-weight: 700; color: #0f2d13; margin-bottom: .25rem; }
    .empty-state p  { font-size: .84rem; color: #52916b; margin-bottom: 0; }

    /* Pagination */
    .appts-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f0fdf4; }
    .appts-pagination .pagination { margin: 0; }
    .appts-pagination .page-link {
        border-radius: 7px !important; border-color: #dcfce7;
        color: #15803d; font-size: .82rem; margin: 0 .1rem;
    }
    .appts-pagination .page-link:hover { background: #dcfce7; border-color: #86efac; }
    .appts-pagination .page-item.active .page-link { background: #16a34a; border-color: #16a34a; color: #fff; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="mb-4">
        <div class="page-title">
            <i class="bi bi-calendar-check me-2" style="color:#16a34a;"></i>Appointments
        </div>
        <p class="page-sub">{{ $office->name }}</p>
    </div>

    {{-- Appointments table --}}
    <div class="appts-card" id="office-appointments-list-root" data-appointments-live data-office-id="{{ $office->id }}">
        @if($appointments->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
                <h6>No appointments found</h6>
                <p>Appointments will appear here once citizens book a time slot.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="appts-table">
                    <thead>
                        <tr>
                            <th>Citizen</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Service Request</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $appointment)
                            @php
                                $pillClass = match($appointment->status) {
                                    'scheduled' => 'pill-scheduled',
                                    'confirmed' => 'pill-confirmed',
                                    'cancelled' => 'pill-cancelled',
                                    'completed' => 'pill-completed',
                                    default     => 'pill-completed',
                                };
                                $pillIcon = match($appointment->status) {
                                    'scheduled' => 'calendar',
                                    'confirmed' => 'check-circle',
                                    'cancelled' => 'x-circle',
                                    'completed' => 'patch-check',
                                    default     => 'circle',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="citizen-name">{{ $appointment->citizen->name ?? '—' }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($appointment->timeSlot->date)->format('d M Y') }}</td>
                                <td style="font-size:.83rem;white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}
                                </td>
                                <td>
                                    @if($appointment->serviceRequest)
                                        <a href="{{ route('office.requests.show', [$office, $appointment->serviceRequest]) }}" class="req-link">
                                            <i class="bi bi-file-earmark-text"></i>#{{ $appointment->serviceRequest->id }}
                                        </a>
                                    @else
                                        <span style="color:#86efac;font-size:.82rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $pillClass }}">
                                        <i class="bi bi-{{ $pillIcon }}"></i>
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($appointment->status === 'scheduled')
                                        <div class="act-group">
                                            <form method="POST"
                                                  action="{{ route('office.appointments.confirm', [$office, $appointment]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-confirm">
                                                    <i class="bi bi-check-lg"></i>Confirm
                                                </button>
                                            </form>
                                            <button type="button"
                                                    class="btn-cancel-appt"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelModal"
                                                    data-action="{{ route('office.appointments.cancel', [$office, $appointment]) }}">
                                                <i class="bi bi-x-lg"></i>Cancel
                                            </button>
                                        </div>
                                    @else
                                        <span style="color:#86efac;font-size:.82rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($appointments->hasPages())
                <div class="appts-pagination">{{ $appointments->links() }}</div>
            @endif
        @endif
    </div>

@endsection

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="cancelForm">
            @csrf
            @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0" style="background:#f0fdf4;border-radius:1rem 1rem 0 0;">
                    <h5 class="modal-title fw-bold" id="cancelModalLabel">
                        <i class="bi bi-x-circle me-2 text-danger"></i>Cancel Appointment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="cancellation_reason" class="form-label fw-semibold" style="font-size:.85rem;">
                        Reason for Cancellation <span class="text-danger">*</span>
                    </label>
                    <textarea id="cancellation_reason"
                              name="cancellation_reason"
                              class="form-control"
                              rows="4"
                              maxlength="500"
                              placeholder="Please provide a reason..."
                              required
                              style="border-color:#d1fae5;"></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-sm">
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
