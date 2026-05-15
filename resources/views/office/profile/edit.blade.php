@extends('layouts.office')

@section('title', 'Edit — '.$office->name)

@include('partials.maps.leaflet-assets')

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    .btn-back {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #f0fdf4; border: 1px solid #d1fae5; color: #15803d;
        font-weight: 600; font-size: .84rem; padding: .44rem 1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-back:hover { background: #dcfce7; }

    /* Municipality note */
    .muni-note {
        display: inline-flex; align-items: center; gap: .4rem;
        font-size: .82rem; color: #52916b; background: #f0fdf4;
        border: 1px solid #d1fae5; border-radius: 8px;
        padding: .4rem .85rem; margin-bottom: 1rem;
    }

    /* Main card */
    .form-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        padding: 1.6rem;
    }

    /* Section heading */
    .section-heading {
        font-size: .95rem; font-weight: 700; color: #0f2d13;
        border-bottom: 1px solid #f0fdf4; padding-bottom: .5rem;
        margin-top: .5rem; margin-bottom: .25rem;
    }

    /* Field labels */
    .form-label { font-size: .82rem; font-weight: 600; color: #14532d; }
    .form-control,
    .form-select { border-color: #d1fae5; font-size: .875rem; }
    .form-control:focus,
    .form-select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
    .form-text { font-size: .74rem; color: #52916b; }

    /* Working hours day box */
    .day-box {
        background: #fff; border: 1px solid #d1fae5; border-radius: 10px;
        padding: .85rem 1rem; height: 100%;
        transition: border-color .15s;
    }
    .day-box:focus-within { border-color: #86efac; }
    .day-box .day-name { font-weight: 700; font-size: .85rem; color: #0f2d13; margin-bottom: .5rem; }
    .day-box .form-check-label { font-size: .8rem; color: #374151; }
    .day-box .form-label { font-size: .74rem; color: #52916b; margin-bottom: .15rem; }

    /* Map shell */
    .leaflet-map-shell { border-radius: 10px; overflow: hidden; border: 1px solid #d1fae5; margin-top: .4rem; }

    /* Submit button */
    .btn-save {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .9rem; padding: .55rem 1.5rem;
        border-radius: 9px; transition: background .15s; cursor: pointer;
    }
    .btn-save:hover { background: #15803d; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="page-title">{{ __('ui.office_profile_edit_title') }}</div>
            <p class="page-sub">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.profile.index') }}" class="btn-back">
            <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>{{ __('ui.office_profile_edit_all_offices') }}
        </a>
    </div>

    @if($office->municipality)
        <div class="muni-note">
            <i class="bi bi-pin-map"></i>
            {{ __('ui.office_profile_edit_muni') }} <strong>{{ $office->municipality->name }}</strong>
        </div>
    @endif

    <div class="form-card">
        <form method="POST" action="{{ route('office.profile.update', $office) }}" class="row g-3">
            @csrf
            @method('PUT')

            {{-- Basic info --}}
            <div class="col-md-6">
                <label class="form-label">{{ __('ui.office_profile_edit_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $office->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <p class="form-text mb-0">{{ __('ui.office_profile_edit_name_note') }}</p>
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('ui.office_profile_edit_address') }} <span class="text-danger">*</span></label>
                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                       value="{{ old('address', $office->address) }}" required>
                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">{{ __('ui.office_profile_edit_email') }}</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $office->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('ui.office_profile_edit_phone') }}</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone', $office->phone) }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('ui.office_profile_edit_website') }}</label>
                <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                       value="{{ old('website', $office->website) }}" placeholder="https://">
                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('ui.office_profile_edit_gmaps') }}</label>
                <input type="url" name="google_maps_url" class="form-control @error('google_maps_url') is-invalid @enderror"
                       value="{{ old('google_maps_url', $office->google_maps_url) }}"
                       placeholder="https://maps.google.com/...">
                @error('google_maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">{{ __('ui.office_profile_edit_lat') }}</label>
                <input type="text" name="latitude" id="office-latitude-input"
                       class="form-control @error('latitude') is-invalid @enderror"
                       value="{{ old('latitude', $office->latitude) }}" placeholder="e.g. 33.8938">
                @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('ui.office_profile_edit_lng') }}</label>
                <input type="text" name="longitude" id="office-longitude-input"
                       class="form-control @error('longitude') is-invalid @enderror"
                       value="{{ old('longitude', $office->longitude) }}" placeholder="e.g. 35.5018">
                @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('ui.office_profile_edit_place_search') }}</label>
                <input type="search" class="form-control" id="office-nominatim-query"
                       placeholder="Type an address and pause — results appear below" autocomplete="off">
                <p class="form-text mt-1 mb-2">Search is debounced and should not be automated (Nominatim fair-use). Drag the pin or click the map to fine-tune.</p>

                <div id="office-nominatim-results" class="list-group small mb-2"></div>
                <div class="leaflet-map-shell">
                    <div id="office-profile-map" class="leaflet-map leaflet-map-sm" role="application" aria-label="Map to set office coordinates"></div>
                </div>
            </div>

            {{-- Working hours --}}
            <div class="col-12">
                <h3 class="section-heading">{{ __('ui.office_profile_edit_hours') }}</h3>
                <p class="form-text">{{ __('ui.office_profile_edit_hours_note') }}</p>
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
                    <div class="day-box">
                        <div class="day-name">{{ $day }}</div>
                        <input type="hidden" name="{{ $closedName }}" value="0">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="{{ $closedName }}" value="1" id="{{ $closedName }}"
                                   @checked((string) old($closedName, $closedDefault) === '1')>
                            <label class="form-check-label" for="{{ $closedName }}">{{ __('ui.office_profile_edit_closed') }}</label>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('ui.office_profile_edit_opens') }}</label>
                                <input type="time" name="wh_{{ $day }}_open" class="form-control form-control-sm"
                                       value="{{ $openVal }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('ui.office_profile_edit_closes') }}</label>
                                <input type="time" name="wh_{{ $day }}_close" class="form-control form-control-sm"
                                       value="{{ $closeVal }}">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Extra contact --}}
            <div class="col-12">
                <h3 class="section-heading">{{ __('ui.office_profile_edit_extra') }}</h3>
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('ui.office_profile_edit_fax') }}</label>
                <input type="text" name="contact_fax" class="form-control" value="{{ old('contact_fax', $contact['fax'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('ui.office_profile_edit_hotline') }}</label>
                <input type="text" name="contact_hotline" class="form-control" value="{{ old('contact_hotline', $contact['hotline'] ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">{{ __('ui.office_profile_edit_notes') }}</label>
                <input type="text" name="contact_notes" class="form-control" value="{{ old('contact_notes', $contact['notes'] ?? '') }}">
            </div>

            <div class="col-12 pt-2">
                <button type="submit" class="btn-save">
                    <i class="bi bi-check2-circle"></i>{{ __('ui.office_profile_edit_save') }}
                </button>
            </div>
        </form>
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
