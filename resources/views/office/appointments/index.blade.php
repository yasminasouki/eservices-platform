@extends('layouts.office')

@section('title', 'Time Slots — ' . $office->name)

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* Add slot form card */
    .add-slot-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d1fae5;
        box-shadow: 0 1px 8px rgba(21,128,61,.05);
        padding: 1.25rem 1.4rem; margin-bottom: 1.25rem;
    }
    .add-slot-card h6 { font-weight: 700; font-size: .9rem; color: #0f2d13; margin-bottom: .9rem; }
    .add-slot-card .form-label { font-size: .78rem; font-weight: 600; color: #52916b; margin-bottom: .25rem; }
    .add-slot-card .form-control { border-color: #d1fae5; font-size: .875rem; }
    .add-slot-card .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }

    .btn-add-slot {
        display: inline-flex; align-items: center; justify-content: center; gap: .35rem;
        background: #16a34a; border: none; color: #fff; width: 100%;
        font-weight: 700; font-size: .875rem; padding: .5rem 1rem;
        border-radius: 9px; transition: background .15s; cursor: pointer;
    }
    .btn-add-slot:hover { background: #15803d; }

    /* Slots table card */
    .slots-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }

    .slots-table { width: 100%; border-collapse: collapse; }
    .slots-table thead th {
        background: #f0fdf4; font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .07em; color: #86efac;
        padding: .65rem 1rem; border-bottom: 1px solid #d1fae5; white-space: nowrap;
    }
    .slots-table thead th:first-child { padding-left: 1.4rem; }
    .slots-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }
    .slots-table tbody tr { border-bottom: 1px solid #f0fdf4; transition: background .12s; }
    .slots-table tbody tr:last-child { border-bottom: none; }
    .slots-table tbody tr:hover { background: #f0fdf4; }
    .slots-table tbody td {
        padding: .85rem 1rem; vertical-align: middle; font-size: .875rem; color: #374151;
    }
    .slots-table tbody td:first-child { padding-left: 1.4rem; }
    .slots-table tbody td:last-child  { padding-right: 1.4rem; }

    /* Status pills */
    .status-pill {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 700;
        padding: .28rem .7rem; border-radius: 999px; white-space: nowrap;
    }
    .pill-available { background: #dcfce7; color: #14532d; }
    .pill-booked    { background: #fef3c7; color: #92400e; }

    /* Action buttons */
    .act-btn-delete {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 7px; border: 1px solid #fecaca;
        background: #fef2f2; color: #991b1b; cursor: pointer; white-space: nowrap;
        transition: background .12s;
    }
    .act-btn-delete:hover { background: #fde8e8; }
    .act-btn-cancel-appt {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .74rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 7px; border: 1px solid #fecaca;
        background: #fef2f2; color: #991b1b; cursor: pointer; white-space: nowrap;
        transition: background .12s;
    }
    .act-btn-cancel-appt:hover { background: #fde8e8; }

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
    .slots-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f0fdf4; }
    .slots-pagination .pagination { margin: 0; }
    .slots-pagination .page-link {
        border-radius: 7px !important; border-color: #dcfce7;
        color: #15803d; font-size: .82rem; margin: 0 .1rem;
    }
    .slots-pagination .page-link:hover { background: #dcfce7; border-color: #86efac; }
    .slots-pagination .page-item.active .page-link { background: #16a34a; border-color: #16a34a; color: #fff; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="mb-4">
        <div class="page-title">
            <i class="bi bi-calendar-plus me-2" style="color:#16a34a;"></i>Time Slots
        </div>
        <p class="page-sub">{{ $office->name }}</p>
    </div>

    {{-- Add Slot Form --}}
    <div class="add-slot-card">
        <h6>Add New Time Slot</h6>
        <form method="POST" action="{{ route('office.slots.store', $office) }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-sm-4">
                <label class="form-label">Date</label>
                <input type="date" name="date"
                       class="form-control @error('date') is-invalid @enderror"
                       value="{{ old('date') }}"
                       min="{{ date('Y-m-d') }}" required>
                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-3">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time"
                       class="form-control @error('start_time') is-invalid @enderror"
                       value="{{ old('start_time') }}" required>
                @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-3">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time"
                       class="form-control @error('end_time') is-invalid @enderror"
                       value="{{ old('end_time') }}" required>
                @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn-add-slot">
                    <i class="bi bi-plus-lg"></i>Add Slot
                </button>
            </div>
        </form>
    </div>

    {{-- Slots Table --}}
    <div class="slots-card" id="office-slots-root" data-appointments-live data-office-id="{{ $office->id }}">
        @forelse($slots as $slot)
            @if($loop->first)
                <div class="table-responsive">
                    <table class="slots-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            @endif
                            <tr>
                                <td style="font-weight:600;color:#0f2d13;">{{ \Carbon\Carbon::parse($slot->date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                <td>
                                    @if($slot->is_available)
                                        <span class="status-pill pill-available">
                                            <i class="bi bi-check-circle"></i> Available
                                        </span>
                                    @else
                                        <span class="status-pill pill-booked">
                                            <i class="bi bi-calendar-check"></i> Booked
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($slot->is_available)
                                        <form method="POST"
                                              action="{{ route('office.slots.destroy', [$office, $slot]) }}"
                                              onsubmit="return confirm('Delete this time slot?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act-btn-delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    @elseif($slot->appointment && in_array($slot->appointment->status, ['scheduled', 'confirmed'], true))
                                        <button type="button"
                                                class="act-btn-cancel-appt"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cancelSlotAppointmentModal"
                                                data-action="{{ route('office.appointments.cancel', [$office, $slot->appointment]) }}"
                                                data-slot-label="{{ \Carbon\Carbon::parse($slot->date)->format('d M Y') }} {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}">
                                            <i class="bi bi-x-circle"></i> Cancel appointment
                                        </button>
                                    @else
                                        <span style="color:#86efac;font-size:.82rem;">—</span>
                                    @endif
                                </td>
                            </tr>
            @if($loop->last)
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
                <h6>No time slots yet</h6>
                <p>Use the form above to add your first slot.</p>
            </div>
        @endforelse

        @if($slots->hasPages())
            <div class="slots-pagination">{{ $slots->links() }}</div>
        @endif
    </div>

@endsection

{{-- Cancel appointment from slot modal --}}
<div class="modal fade" id="cancelSlotAppointmentModal" tabindex="-1" aria-labelledby="cancelSlotAppointmentLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="cancelSlotAppointmentForm">
            @csrf
            @method('PATCH')
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0" style="background:#f0fdf4;border-radius:1rem 1rem 0 0;">
                    <h5 class="modal-title fw-bold" id="cancelSlotAppointmentLabel">
                        <i class="bi bi-x-circle me-2 text-danger"></i>Cancel booked appointment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:.84rem;color:#52916b;" class="mb-2">
                        Slot: <span id="cancelSlotAppointmentLabelText" class="fw-semibold text-body">—</span>
                    </p>
                    <label for="cancel_slot_reason" class="form-label fw-semibold" style="font-size:.85rem;">
                        Reason for cancellation <span class="text-danger">*</span>
                    </label>
                    <textarea id="cancel_slot_reason"
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
                        <i class="bi bi-x-lg me-1"></i>Cancel appointment
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

    function refreshSlotsSection() {
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
                var incoming = doc.getElementById('office-slots-root');
                var current = document.getElementById('office-slots-root');
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
        refreshSlotsSection();
    });

    var cancelModal = document.getElementById('cancelSlotAppointmentModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var action = trigger ? trigger.dataset.action : '';
            var slotLabel = trigger ? (trigger.dataset.slotLabel || '—') : '—';
            var form = document.getElementById('cancelSlotAppointmentForm');
            var slotText = document.getElementById('cancelSlotAppointmentLabelText');
            var reason = document.getElementById('cancel_slot_reason');

            if (form) {
                form.action = action;
            }
            if (slotText) {
                slotText.textContent = slotLabel;
            }
            if (reason) {
                reason.value = '';
            }
        });
    }

    setInterval(function () {
        if (document.visibilityState === 'visible') {
            refreshSlotsSection();
        }
    }, 5000);
})();
</script>
@endpush
