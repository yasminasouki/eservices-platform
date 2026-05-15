@extends('layouts.citizen')
@section('title', 'My Appointments')

@push('styles')
<style>
    .appt-card {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #ede9fe;
        box-shadow: 0 2px 12px rgba(124,58,237,.06);
        padding: 1.1rem 1.25rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: flex-start;
        gap: 1.1rem;
        flex-wrap: wrap;
    }
    .appt-date-box {
        background: #f3f0ff;
        border: 1px solid #ddd6fe;
        border-radius: 12px;
        min-width: 64px;
        text-align: center;
        padding: .6rem .75rem;
        flex-shrink: 0;
    }
    .appt-date-day   { font-size: 1.5rem; font-weight: 800; color: #4c1d95; line-height: 1; }
    .appt-date-month { font-size: .7rem; font-weight: 700; text-transform: uppercase; color: #9d7ecf; letter-spacing: .05em; margin-top: .1rem; }
    .appt-date-year  { font-size: .68rem; color: #b4a0d4; }
    .appt-info { flex: 1; min-width: 0; }
    .appt-office { font-weight: 700; font-size: .95rem; color: #1f1235; margin-bottom: .2rem; }
    .appt-time   { font-size: .82rem; color: #6d5b8e; margin-bottom: .35rem; }
    .appt-badge  {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; font-weight: 700; padding: .22rem .65rem;
        border-radius: 999px;
    }
    .appt-notes { font-size: .8rem; color: #64748b; margin-top: .4rem; }
    .empty-state { text-align: center; padding: 4rem 1rem; color: #9d7ecf; }
    .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 1rem; }
    .empty-state p { font-size: .9rem; color: #b4a0d4; margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-800 mb-0" style="color:#1f1235;">My Appointments</h4>
            <p class="text-muted small mb-0">All your scheduled visits with government offices.</p>
        </div>
        <a href="{{ route('citizen.offices.index') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Book new
        </a>
    </div>

    @if($appointments->isEmpty())
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <p>You have no appointments yet.</p>
            <a href="{{ route('citizen.offices.index') }}" class="btn btn-primary">
                <i class="bi bi-building me-2"></i>Browse offices & book
            </a>
        </div>
    @else
        @foreach($appointments as $appt)
            @php
                $slot   = $appt->timeSlot;
                $date   = $slot?->date;
                $status = $appt->status ?? 'booked';
                [$bg, $color, $icon] = match($status) {
                    'confirmed'  => ['#dcfce7', '#14532d', 'check-circle-fill'],
                    'cancelled'  => ['#fde8e8', '#991b1b', 'x-circle-fill'],
                    'completed'  => ['#ede9fe', '#4c1d95', 'patch-check-fill'],
                    default      => ['#fef3c7', '#92400e', 'clock-fill'],
                };
            @endphp
            <div class="appt-card">
                {{-- Date box --}}
                <div class="appt-date-box">
                    @if($date)
                        <div class="appt-date-day">{{ $date->format('d') }}</div>
                        <div class="appt-date-month">{{ $date->format('M') }}</div>
                        <div class="appt-date-year">{{ $date->format('Y') }}</div>
                    @else
                        <div class="appt-date-day">—</div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="appt-info">
                    <div class="appt-office">
                        <i class="bi bi-building me-1 text-muted" style="font-size:.85rem;"></i>
                        {{ $appt->governmentOffice?->name ?? '—' }}
                    </div>
                    <div class="appt-time">
                        <i class="bi bi-clock me-1"></i>
                        {{ $slot ? $slot->start_time . ' – ' . $slot->end_time : '—' }}
                    </div>
                    <span class="appt-badge" style="background:{{ $bg }};color:{{ $color }};">
                        <i class="bi bi-{{ $icon }}"></i>
                        {{ ucfirst($status) }}
                    </span>
                    @if($appt->notes)
                        <div class="appt-notes"><i class="bi bi-chat-left-text me-1"></i>{{ $appt->notes }}</div>
                    @endif
                    @if($status === 'cancelled' && $appt->cancellation_reason)
                        <div class="appt-notes text-danger"><i class="bi bi-info-circle me-1"></i>{{ $appt->cancellation_reason }}</div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="d-flex flex-column gap-2 align-items-end" style="min-width:90px;">
                    <a href="{{ route('citizen.offices.show', $appt->government_office_id) }}"
                       class="btn btn-outline-primary btn-sm" style="font-size:.78rem;">
                        <i class="bi bi-building me-1"></i>Office
                    </a>
                    @if(!in_array($status, ['cancelled', 'completed']) && $appt->governmentOffice)
                        <form method="POST"
                              action="{{ route('citizen.appointments.cancel', [$appt->governmentOffice, $appt]) }}"
                              onsubmit="return confirm('Cancel this appointment?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm" style="font-size:.78rem;background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="mt-3">
            {{ $appointments->links() }}
        </div>
    @endif
@endsection
