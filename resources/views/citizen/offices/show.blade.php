@extends('layouts.citizen')

@section('title', $office->name)

@if($officeMap ?? null)
    @include('partials.maps.leaflet-assets')
@endif

@push('styles')
<style>
    html { scroll-behavior: smooth; }
    #office-appointments { scroll-margin-top: 1rem; }
</style>
@endpush

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">Offices</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $office->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $office->name }}</h2>
            @if($office->address)
                <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $office->address }}</p>
            @endif
            @if($avgRating !== null)
                @php $avgStars = (int) round($avgRating); @endphp
                <p class="small mb-0 mt-2">
                    <span class="text-warning" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $avgStars ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </span>
                    <span class="text-muted ms-1">{{ number_format($avgRating, 1) }} average from visitor feedback</span>
                </p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('citizen.offices.chat', $office) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-chat-dots me-1"></i>Live chat
            </a>
            <a href="#office-appointments" class="btn btn-success btn-sm">
                <i class="bi bi-calendar-plus me-1"></i>Book Appointment
            </a>
            <a href="{{ route('citizen.feedback.office.create', $office) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-star me-1"></i>Leave feedback
            </a>
            <a href="{{ route('citizen.offices.index') }}" class="btn btn-outline-secondary btn-sm">All offices</a>
        </div>
    </div>

    @if($officeMap ?? null)
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold">
                <i class="bi bi-map me-2"></i>Location
            </div>
            <div class="card-body">
                <div class="leaflet-map-shell mb-3">
                    <div id="citizen-office-show-map" class="leaflet-map leaflet-map-sm" role="region" aria-label="Map of office location"></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="https://www.openstreetmap.org/?mlat={{ $officeMap['lat'] }}&mlon={{ $officeMap['lng'] }}#map=16/{{ $officeMap['lat'] }}/{{ $officeMap['lng'] }}"
                       class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">
                        Open in OpenStreetMap
                    </a>
                    @if($office->google_maps_url)
                        <a href="{{ $office->google_maps_url }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">
                            Google Maps link
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @elseif($office->google_maps_url)
        <div class="mb-4">
            <a href="{{ $office->google_maps_url }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-geo-alt me-1"></i>Open map link
            </a>
        </div>
    @endif

    <div class="card card-soft mb-4" id="office-appointments" tabindex="-1">
        <div class="card-header bg-white border-0 fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-calendar-check text-success"></i>
            Appointments
        </div>
        <div class="card-body">
            @if(isset($availableSlots) && $availableSlots->isNotEmpty())
                <p class="text-muted small mb-3 mb-md-2">Choose an open slot below. You will confirm the booking in one step.</p>
                <ul class="list-group list-group-flush rounded border">
                    @foreach($availableSlots as $slot)
                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($slot->date)->format('l, d M Y') }}</div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('citizen.appointments.store', [$office, $slot]) }}" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-calendar-plus me-1"></i>Book this slot
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="alert alert-light border text-muted mb-0 small">
                    <i class="bi bi-calendar-x me-2 text-secondary"></i>
                    <strong class="text-body">No available appointments right now.</strong>
                    This office has not published any open time slots yet, or they are all booked. Please check back later, use <a href="{{ route('citizen.offices.chat', $office) }}">live chat</a>, or try another office.
                </div>
            @endif
        </div>
    </div>

    @if($publicReviews->isNotEmpty())
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold">Recent visitor feedback</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach($publicReviews as $rev)
                        <li class="border-bottom pb-3 mb-3">
                            <div class="text-warning small mb-1" aria-label="{{ $rev->rating }} of 5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $rev->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                            @if($rev->comment)
                                <p class="small mb-2">{{ $rev->comment }}</p>
                            @endif
                            @if($rev->office_reply && $rev->reply_is_public)
                                <div class="small border-start border-3 border-success ps-2 text-muted">
                                    <strong class="text-body">Office:</strong> {{ $rev->office_reply }}
                                </div>
                            @elseif($rev->office_reply && ! $rev->reply_is_public)
                                @if(auth()->id() === $rev->user_id)
                                    <div class="small border-start border-3 border-secondary ps-2 text-muted">
                                        <strong class="text-body">Office (private reply to you):</strong> {{ $rev->office_reply }}
                                    </div>
                                @else
                                    <p class="small text-muted fst-italic mb-0">The office sent a private reply to this visitor.</p>
                                @endif
                            @endif
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $rev->created_at?->format('M j, Y') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($categories->isEmpty())
        <div class="card card-soft">
            <div class="card-body text-muted">This office has not published any services yet.</div>
        </div>
    @else
        @foreach($categories as $category)
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">{{ $category->name }}</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($category->services as $svc)
                            <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $svc->name }}</div>
                                    @if($svc->description)
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($svc->description, 120) }}</div>
                                    @endif
                                    <div class="small mt-1">
                                        <span class="text-muted">Fee:</span>
                                        {{ number_format((float) $svc->price, 2) }}
                                    </div>
                                </div>
                                <a href="{{ route('citizen.services.apply', [$office, $svc]) }}" class="btn btn-sm btn-primary flex-shrink-0">
                                    Request
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@push('scripts')
<script>
(function () {
    function scrollToAppointments() {
        var el = document.getElementById('office-appointments');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            el.focus({ preventScroll: true });
        }
    }
    if (window.location.hash === '#office-appointments') {
        window.requestAnimationFrame(function () {
            setTimeout(scrollToAppointments, 100);
        });
    }
})();
</script>
@endpush

@if($officeMap ?? null)
@push('scripts')
<script>
(function () {
    var cfg = @json($mapConfig ?? []);
    var m = @json($officeMap);
    var el = document.getElementById('citizen-office-show-map');
    if (!el || typeof L === 'undefined' || !m) return;
    var map = L.map(el, { scrollWheelZoom: false }).setView([m.lat, m.lng], 16);
    L.tileLayer(cfg.tileUrl, { attribution: cfg.attribution, maxZoom: cfg.maxZoom }).addTo(map);
    var popup = '<strong>' + (m.name || '').replace(/</g, '&lt;') + '</strong>';
    if (m.address) popup += '<br><span class="small text-muted">' + String(m.address).replace(/</g, '&lt;') + '</span>';
    L.marker([m.lat, m.lng]).addTo(map).bindPopup(popup);
})();
</script>
@endpush
@endif
