@extends('layouts.office')

@section('title', 'Edit — '.$office->name)

@include('partials.maps.leaflet-assets')

@section('content')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit office profile</h2>
            <p class="text-muted mb-0">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.profile.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>All offices
        </a>
    </div>

    @if($office->municipality)
        <p class="small text-muted mb-3">
            <i class="bi bi-pin-map me-1"></i>Municipality (admin-managed): <strong>{{ $office->municipality->name }}</strong>
        </p>
    @endif

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('office.profile.update', $office) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Office name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $office->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label d-block">&nbsp;</label>
                    <span class="text-muted small">Use the official name citizens will see in the directory.</span>
                </div>

                <div class="col-12">
                    <label class="form-label">Address *</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                           value="{{ old('address', $office->address) }}" required>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Public email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $office->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $office->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                           value="{{ old('website', $office->website) }}" placeholder="https://">
                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Google Maps link</label>
                    <input type="url" name="google_maps_url" class="form-control @error('google_maps_url') is-invalid @enderror"
                           value="{{ old('google_maps_url', $office->google_maps_url) }}"
                           placeholder="https://maps.google.com/...">
                    @error('google_maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" id="office-latitude-input"
                           class="form-control @error('latitude') is-invalid @enderror"
                           value="{{ old('latitude', $office->latitude) }}" placeholder="e.g. 33.8938">
                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" id="office-longitude-input"
                           class="form-control @error('longitude') is-invalid @enderror"
                           value="{{ old('longitude', $office->longitude) }}" placeholder="e.g. 35.5018">
                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Place search (OpenStreetMap Nominatim)</label>
                    <input type="search" class="form-control" id="office-nominatim-query"
                           placeholder="Type an address and pause — results appear below" autocomplete="off">
                    <p class="small text-muted mb-2 mt-1">Search is debounced and should not be automated (Nominatim fair-use). Drag the pin or click the map to fine-tune.</p>
                    <div id="office-nominatim-results" class="list-group small mb-2"></div>
                    <div class="leaflet-map-shell">
                        <div id="office-profile-map" class="leaflet-map leaflet-map-sm" role="application" aria-label="Map to set office coordinates"></div>
                    </div>
                </div>

                <div class="col-12">
                    <h3 class="h6 fw-bold border-bottom pb-2 mt-2">Working hours</h3>
                    <p class="small text-muted">Leave a day marked closed or leave times empty to skip it.</p>
                </div>

                @foreach($weekdays as $day)
                    @php
                        $slot = $hoursByDay[$day] ?? null;
                        $closedName = 'wh_'.$day.'_closed';
                        $closedDefault = $slot === null ? '1' : '0';
                        $openVal = old('wh_'.$day.'_open', $slot['open'] ?? '');
                        $closeVal = old('wh_'.$day.'_close', $slot['close'] ?? '');
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <div class="fw-semibold mb-2">{{ $day }}</div>
                            <input type="hidden" name="{{ $closedName }}" value="0">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="{{ $closedName }}" value="1" id="{{ $closedName }}"
                                       @checked((string) old($closedName, $closedDefault) === '1')>
                                <label class="form-check-label small" for="{{ $closedName }}">Closed</label>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-0">Opens</label>
                                    <input type="time" name="wh_{{ $day }}_open" class="form-control form-control-sm"
                                           value="{{ $openVal }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-0">Closes</label>
                                    <input type="time" name="wh_{{ $day }}_close" class="form-control form-control-sm"
                                           value="{{ $closeVal }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="col-12">
                    <h3 class="h6 fw-bold border-bottom pb-2 mt-3">Extra contact</h3>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fax</label>
                    <input type="text" name="contact_fax" class="form-control" value="{{ old('contact_fax', $contact['fax'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hotline</label>
                    <input type="text" name="contact_hotline" class="form-control" value="{{ old('contact_hotline', $contact['hotline'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Internal notes (optional)</label>
                    <input type="text" name="contact_notes" class="form-control" value="{{ old('contact_notes', $contact['notes'] ?? '') }}">
                </div>

                <div class="col-12 pt-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Save profile
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var cfg = @json($mapConfig ?? []);
    var latIn = document.getElementById('office-latitude-input');
    var lngIn = document.getElementById('office-longitude-input');
    var qEl = document.getElementById('office-nominatim-query');
    var resEl = document.getElementById('office-nominatim-results');
    var mapEl = document.getElementById('office-profile-map');
    if (!latIn || !lngIn || !mapEl || typeof L === 'undefined') return;

    function parseNum(v) {
        var n = parseFloat(String(v).replace(',', '.'), 10);
        return isNaN(n) ? null : n;
    }

    function round6(n) {
        return Math.round(n * 1e6) / 1e6;
    }

    function writeInputs(lat, lng) {
        latIn.value = String(round6(lat));
        lngIn.value = String(round6(lng));
    }

    var defLat = cfg.defaultLat;
    var defLng = cfg.defaultLng;
    var lat0 = parseNum(latIn.value);
    var lng0 = parseNum(lngIn.value);
    if (lat0 !== null && lng0 !== null) {
        defLat = lat0;
        defLng = lng0;
    }

    var map = L.map(mapEl, { scrollWheelZoom: true }).setView([defLat, defLng], lat0 !== null && lng0 !== null ? 16 : 11);
    L.tileLayer(cfg.tileUrl, { attribution: cfg.attribution, maxZoom: cfg.maxZoom }).addTo(map);

    var marker = L.marker([defLat, defLng], { draggable: true }).addTo(map);

    marker.on('dragend', function () {
        var p = marker.getLatLng();
        writeInputs(p.lat, p.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        writeInputs(e.latlng.lat, e.latlng.lng);
    });

    function trySyncMarkerFromInputs() {
        var la = parseNum(latIn.value);
        var lo = parseNum(lngIn.value);
        if (la === null || lo === null) return;
        marker.setLatLng([la, lo]);
        map.setView([la, lo], Math.max(map.getZoom(), 14));
    }

    latIn.addEventListener('change', trySyncMarkerFromInputs);
    lngIn.addEventListener('change', trySyncMarkerFromInputs);

    var searchTimer = null;
    var lastReq = 0;

    function showResults(items) {
        resEl.innerHTML = '';
        if (!items || !items.length) return;
        items.forEach(function (it) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'list-group-item list-group-item-action text-start';
            b.textContent = it.display_name || (it.lat + ',' + it.lon);
            b.addEventListener('click', function () {
                var la = parseFloat(it.lat, 10);
                var lo = parseFloat(it.lon, 10);
                if (isNaN(la) || isNaN(lo)) return;
                marker.setLatLng([la, lo]);
                map.setView([la, lo], 16);
                writeInputs(la, lo);
                resEl.innerHTML = '';
                qEl.value = '';
            });
            resEl.appendChild(b);
        });
    }

    if (qEl) {
        qEl.addEventListener('input', function () {
            var q = qEl.value.trim();
            if (searchTimer) clearTimeout(searchTimer);
            resEl.innerHTML = '';
            if (q.length < 3) return;
            searchTimer = setTimeout(function () {
                var now = Date.now();
                var wait = Math.max(0, 1100 - (now - lastReq));
                setTimeout(function () {
                    lastReq = Date.now();
                    var url = cfg.nominatim + '?format=json&q=' + encodeURIComponent(q) + '&limit=5';
                    fetch(url, { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(showResults)
                        .catch(function () {
                            resEl.innerHTML = '';
                        });
                }, wait);
            }, 600);
        });
    }
})();
</script>
@endpush
