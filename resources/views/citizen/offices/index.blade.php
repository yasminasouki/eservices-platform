@extends('layouts.citizen')

@section('title', 'Browse offices')

@php $markersJson = $mapMarkers ?? collect(); @endphp

@include('partials.maps.leaflet-assets')

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #1f1235; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #9d7ecf; margin: 0; }

    /* Map card */
    .map-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(124,58,237,.06);
        border: 1px solid #f0ecff;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .map-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem 1.4rem .85rem;
        border-bottom: 1px solid #f3f0ff;
    }
    .map-card-title {
        font-weight: 700; font-size: .9rem; color: #1f1235;
        display: flex; align-items: center; gap: .5rem;
    }
    .map-card-title i { color: #a78bfa; }
    .map-status {
        font-size: .78rem; color: #b4a0d4;
        padding: .6rem 1.4rem .5rem;
        display: flex; align-items: center; gap: .4rem;
    }
    .map-status i { color: #c4b5fd; }

    .map-card-body { padding: 0 1.4rem 1rem; }
    .leaflet-map-shell { border-radius: 12px; overflow: hidden; border: 1px solid #ede9fe; }
    .map-credit { font-size: .72rem; color: #b4a0d4; margin-top: .5rem; }
    .map-credit a { color: #a78bfa; }

    /* Map buttons */
    .btn-location {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #ede9fe; border: 1px solid #ddd6fe;
        color: #4c1d95; font-size: .8rem; font-weight: 700;
        padding: .4rem .9rem; border-radius: 9px;
        cursor: pointer; transition: background .15s;
    }
    .btn-location:hover { background: #ddd6fe; }
    .btn-sort {
        display: none;
        align-items: center; gap: .4rem;
        background: #f3f4f6; border: 1px solid #e5e7eb;
        color: #374151; font-size: .8rem; font-weight: 600;
        padding: .4rem .9rem; border-radius: 9px;
        cursor: pointer; transition: background .15s;
    }
    .btn-sort.show { display: inline-flex; }
    .btn-sort:hover { background: #e5e7eb; }

    /* Offices card */
    .offices-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(124,58,237,.06);
        border: 1px solid #f0ecff;
        overflow: hidden;
    }
    .offices-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.4rem;
        border-bottom: 1px solid #f3f0ff;
    }
    .offices-card-title {
        font-weight: 700; font-size: .9rem; color: #1f1235;
        display: flex; align-items: center; gap: .5rem;
    }
    .offices-card-title i { color: #a78bfa; }
    .total-badge {
        background: #ede9fe; color: #6d28d9;
        font-size: .72rem; font-weight: 700;
        padding: .15rem .55rem; border-radius: 999px;
    }

    /* Table */
    .offices-table { width: 100%; border-collapse: collapse; }
    .offices-table thead th {
        background: #faf8ff;
        font-size: .72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em;
        color: #b4a0d4;
        padding: .7rem 1rem;
        border-bottom: 1px solid #f0ecff;
        white-space: nowrap;
    }
    .offices-table thead th:first-child { padding-left: 1.4rem; }
    .offices-table thead th:last-child  { padding-right: 1.4rem; text-align: right; }

    .offices-table tbody tr {
        border-bottom: 1px solid #faf9ff;
        transition: background .12s;
    }
    .offices-table tbody tr:last-child { border-bottom: none; }
    .offices-table tbody tr:hover { background: #faf8ff; }

    .offices-table tbody td {
        padding: .9rem 1rem;
        vertical-align: middle;
        font-size: .875rem;
        color: #374151;
    }
    .offices-table tbody td:first-child { padding-left: 1.4rem; }
    .offices-table tbody td:last-child  { padding-right: 1.4rem; }

    .office-name   { font-weight: 700; color: #1f1235; margin-bottom: .1rem; }
    .office-muni   { font-size: .77rem; color: #b4a0d4; display: flex; align-items: center; gap: .25rem; }

    .services-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #f3f0ff; color: #6d28d9;
        font-size: .75rem; font-weight: 700;
        padding: .25rem .6rem; border-radius: 999px;
    }

    .distance-chip {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #f3f4f6; color: #6b7280;
        font-size: .75rem; font-weight: 600;
        padding: .25rem .6rem; border-radius: 999px;
    }

    .btn-view-services {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #c4b5fd; border: none;
        color: #3b0764; font-size: .8rem; font-weight: 700;
        padding: .45rem 1rem; border-radius: 9px;
        text-decoration: none; transition: background .15s;
        white-space: nowrap;
    }
    .btn-view-services:hover { background: #a78bfa; color: #3b0764; }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 4rem 2rem;
    }
    .empty-icon {
        width: 72px; height: 72px; background: #f3f0ff;
        border-radius: 50%; display: flex; align-items: center;
        justify-content: center; color: #c4b5fd;
        font-size: 1.9rem; margin: 0 auto 1.1rem;
    }
    .empty-state h6 { font-weight: 700; color: #1f1235; margin-bottom: .3rem; }
    .empty-state p  { font-size: .84rem; color: #b4a0d4; margin: 0; }

    /* Pagination */
    .offices-pagination { padding: .85rem 1.4rem; border-top: 1px solid #f3f0ff; }
    .offices-pagination .page-link {
        border-radius: 7px !important; border-color: #ede9fe;
        color: #7c3aed; font-size: .82rem; margin: 0 .1rem;
    }
    .offices-pagination .page-link:hover { background: #ede9fe; border-color: #c4b5fd; }
    .offices-pagination .page-item.active .page-link {
        background: #c4b5fd; border-color: #c4b5fd; color: #3b0764;
    }
</style>
@endpush

@section('content')

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <div class="page-title">Browse offices</div>
            <p class="page-sub">Choose an office to see available services. Enable location to sort by distance.</p>
        </div>
    </div>

    {{-- Map card --}}
    @if($markersJson->isNotEmpty())
        <div class="map-card">
            <div class="map-card-header">
                <div class="map-card-title">
                    <i class="bi bi-map"></i> Office locations
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-location" id="citizen-offices-use-location">
                        <i class="bi bi-geo-alt-fill"></i> Use my location
                    </button>
                    <button type="button" class="btn-sort" id="citizen-offices-sort-nearest">
                        <i class="bi bi-sort-down"></i> Sort by nearest
                    </button>
                </div>
            </div>
            <div class="map-status" id="citizen-offices-location-status">
                <i class="bi bi-info-circle"></i>
                Location is not used until you click the button above.
            </div>
            <div class="map-card-body">
                <div class="leaflet-map-shell">
                    <div id="citizen-offices-map" class="leaflet-map"
                         role="region" aria-label="Map of government office locations"></div>
                </div>
                <p class="map-credit mt-2 mb-0">
                    Map data &copy; <a href="https://www.openstreetmap.org/copyright" rel="noopener noreferrer">OpenStreetMap</a> contributors.
                </p>
            </div>
        </div>
    @else
        <div class="map-card" style="padding:1.1rem 1.4rem;">
            <div class="d-flex align-items-center gap-2" style="font-size:.84rem;color:#b4a0d4;">
                <i class="bi bi-map" style="color:#c4b5fd;"></i>
                No offices have map coordinates yet.
            </div>
        </div>
    @endif

    {{-- Search / filter bar --}}
    <form method="GET" action="{{ route('citizen.offices.index') }}" class="mb-3" id="offices-filter-form">
        <div class="d-flex gap-2 flex-wrap">
            <div style="flex:1;min-width:180px;position:relative;">
                <i class="bi bi-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:#b4a0d4;font-size:.9rem;pointer-events:none;"></i>
                <input type="text" name="search" id="offices-search" value="{{ $search }}"
                       placeholder="Search offices…"
                       class="form-control" autocomplete="off"
                       style="padding-left:2.2rem;border-color:#ddd6fe;border-radius:10px;font-size:.875rem;">
            </div>
            <select name="municipality" id="offices-municipality" class="form-select" style="max-width:200px;border-color:#ddd6fe;border-radius:10px;font-size:.875rem;color:#6d5b8e;">
                <option value="">All municipalities</option>
                @foreach($municipalities as $m)
                    <option value="{{ $m->id }}" @selected($municipalityId == $m->id)>{{ $m->name }}</option>
                @endforeach
            </select>
            @if($search || $municipalityId)
                <a href="{{ route('citizen.offices.index') }}" class="btn btn-outline-secondary" style="border-radius:10px;padding:.45rem .9rem;font-size:.875rem;border-color:#ddd6fe;color:#9d7ecf;">
                    <i class="bi bi-x-lg"></i>
                </a>
            @endif
        </div>
    </form>

    {{-- Offices table card --}}
    <div class="offices-card">
        <div class="offices-card-header">
            <div class="offices-card-title">
                <i class="bi bi-building"></i>
                Government offices
                <span class="total-badge">{{ $offices->total() }}</span>
            </div>
        </div>

        @if($offices->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-building"></i></div>
                @if($search || $municipalityId)
                    <h6>No offices found</h6>
                    <p>Try a different search term or municipality.</p>
                    <a href="{{ route('citizen.offices.index') }}" class="btn btn-sm btn-primary mt-2">Clear filters</a>
                @else
                    <h6>No offices available</h6>
                    <p>No active government offices are listed yet. Check back later.</p>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="offices-table" id="citizen-offices-table">
                    <thead>
                        <tr>
                            <th>Office</th>
                            <th>Municipality</th>
                            <th>Services</th>
                            <th class="distance-col">Distance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offices as $o)
                            @php $hasCoords = $o->latitude !== null && $o->longitude !== null; @endphp
                            <tr
                                @if($hasCoords)
                                    data-office-id="{{ $o->id }}"
                                    data-lat="{{ $o->latitude }}"
                                    data-lng="{{ $o->longitude }}"
                                @endif
                            >
                                <td>
                                    <div class="office-name">{{ $o->name }}</div>
                                </td>
                                <td>
                                    <div class="office-muni">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $o->municipality?->name ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="services-chip">
                                        <i class="bi bi-grid-3x3-gap"></i>
                                        {{ $o->services_count }}
                                    </span>
                                </td>
                                <td class="distance-col" data-distance-cell>
                                    <span class="distance-chip">—</span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="{{ route('citizen.offices.show', $o) }}" class="btn-view-services">
                                        View services <i class="bi bi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($offices->hasPages())
                <div class="offices-pagination">{{ $offices->links() }}</div>
            @endif
        @endif
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var form  = document.getElementById('offices-filter-form');
    var input = document.getElementById('offices-search');
    var select = document.getElementById('offices-municipality');
    var timer;

    if (input) {
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { form.submit(); }, 400);
        });
    }

    if (select) {
        select.addEventListener('change', function () { form.submit(); });
    }
})();
</script>
@endpush

@if($markersJson->isNotEmpty())
@push('scripts')
<script>
(function () {
    function haversineKm(lat1, lon1, lat2, lon2) {
        var R = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLon = (lon2 - lon1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function fmtKm(km) {
        return km < 1 ? Math.round(km * 1000) + ' m' : km.toFixed(1) + ' km';
    }

    var cfg        = @json($mapConfig ?? []);
    var rawMarkers = @json($markersJson);

    function initMap() {
        var mapEl = document.getElementById('citizen-offices-map');
        if (!mapEl || typeof L === 'undefined') return;

        var latlngs = rawMarkers.map(function (m) { return [m.lat, m.lng]; });
        var map = L.map(mapEl, { scrollWheelZoom: false });
        L.tileLayer(cfg.tileUrl, {
            attribution: cfg.attribution,
            maxZoom: cfg.maxZoom,
            subdomains: 'abcd',
        }).addTo(map);

        var bounds = L.latLngBounds(latlngs);
        if (bounds.isValid()) {
            map.fitBounds(bounds.pad(0.12));
        } else {
            map.setView([cfg.defaultLat, cfg.defaultLng], 11);
        }

        var layer = L.layerGroup().addTo(map);

        function popupHtml(m) {
            var esc = function (s) {
                if (!s) return '';
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            };
            return '<strong>' + esc(m.name) + '</strong>' +
                (m.address ? '<br><span style="font-size:.8rem;color:#9d7ecf;">' + esc(m.address) + '</span>' : '') +
                '<br><span style="font-size:.8rem;">' + m.services + ' service(s)</span>' +
                '<br><a href="' + esc(m.url) + '" style="color:#7c3aed;font-size:.8rem;font-weight:600;">View services →</a>';
        }

        rawMarkers.forEach(function (m) {
            L.marker([m.lat, m.lng]).bindPopup(popupHtml(m)).addTo(layer);
        });

        var userMarker = null, userLat = null, userLng = null;
        var statusEl   = document.getElementById('citizen-offices-location-status');
        var sortBtn    = document.getElementById('citizen-offices-sort-nearest');
        var tbody      = document.querySelector('#citizen-offices-table tbody');

        function applyDistances() {
            if (userLat == null) return;
            document.querySelectorAll('#citizen-offices-table tbody tr[data-lat]').forEach(function (row) {
                var lat  = parseFloat(row.getAttribute('data-lat'));
                var lng  = parseFloat(row.getAttribute('data-lng'));
                var cell = row.querySelector('[data-distance-cell]');
                if (!cell || isNaN(lat) || isNaN(lng)) return;
                var km = haversineKm(userLat, userLng, lat, lng);
                cell.innerHTML = '<span class="distance-chip"><i class="bi bi-geo-alt-fill" style="color:#a78bfa;font-size:.7rem;"></i>' + fmtKm(km) + '</span>';
                row.setAttribute('data-distance-km', String(km));
            });
        }

        function sortRowsByDistance() {
            if (!tbody || userLat == null) return;
            var rows = Array.from(tbody.querySelectorAll('tr'));
            rows.sort(function (a, b) {
                var da = parseFloat(a.getAttribute('data-distance-km')) || 1e9;
                var db = parseFloat(b.getAttribute('data-distance-km')) || 1e9;
                return da - db;
            });
            rows.forEach(function (r) { tbody.appendChild(r); });
        }

        document.getElementById('citizen-offices-use-location').addEventListener('click', function () {
            if (!navigator.geolocation) {
                if (statusEl) statusEl.innerHTML = '<i class="bi bi-exclamation-circle" style="color:#fca5a5;"></i> Your browser does not support geolocation.';
                return;
            }
            if (statusEl) statusEl.innerHTML = '<i class="bi bi-arrow-repeat" style="color:#c4b5fd;"></i> Requesting location…';
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    userLat = pos.coords.latitude;
                    userLng = pos.coords.longitude;
                    if (userMarker) layer.removeLayer(userMarker);
                    userMarker = L.circleMarker([userLat, userLng], {
                        radius: 10, fillColor: '#7c3aed', color: '#fff',
                        weight: 2, opacity: 1, fillOpacity: 0.9,
                    }).bindPopup('Your location').addTo(layer);
                    try {
                        map.fitBounds(L.latLngBounds(latlngs.concat([[userLat, userLng]])).pad(0.15));
                    } catch (e) { map.setView([userLat, userLng], 12); }
                    if (statusEl) statusEl.innerHTML = '<i class="bi bi-check-circle" style="color:#6ee7b7;"></i> Showing distance from your location (approximate).';
                    applyDistances();
                    if (sortBtn) sortBtn.classList.add('show');
                },
                function () {
                    if (statusEl) statusEl.innerHTML = '<i class="bi bi-exclamation-circle" style="color:#fca5a5;"></i> Could not read location. Check browser permissions.';
                },
                { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
            );
        });

        if (sortBtn) sortBtn.addEventListener('click', sortRowsByDistance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap);
    } else {
        initMap();
    }
})();
</script>
@endpush
@endif
