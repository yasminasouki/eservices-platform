@extends('layouts.citizen')

@section('title', 'Government offices')

@php
    $markersJson = $mapMarkers ?? collect();
@endphp

@include('partials.maps.leaflet-assets')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Browse offices</h2>
            <p class="text-muted small mb-0">Choose an office to see available public services. Use the map to explore locations; enable location to sort by distance.</p>
        </div>
        <a href="{{ route('citizen.dashboard') }}" class="btn btn-outline-secondary btn-sm">Dashboard</a>
    </div>

    @if($markersJson->isNotEmpty())
        <div class="card card-soft mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="fw-semibold">Office locations</div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="citizen-offices-use-location">
                            <i class="bi bi-geo-alt-fill me-1"></i>Use my location
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="citizen-offices-sort-nearest">
                            Sort list by nearest
                        </button>
                    </div>
                </div>
                <p class="small text-muted mb-2" id="citizen-offices-location-status">Location is not used until you choose the button above.</p>
                <div class="leaflet-map-shell">
                    <div id="citizen-offices-map" class="leaflet-map" role="region" aria-label="Map of government office locations"></div>
                </div>
                <p class="small text-muted mb-0 mt-2">Map data &copy; <a href="https://www.openstreetmap.org/copyright" rel="noopener noreferrer">OpenStreetMap</a> contributors.</p>
            </div>
        </div>
    @else
        <div class="alert alert-light border small mb-4">
            No offices have map coordinates yet. Ask your municipality to add latitude and longitude on each office profile so they appear here.
        </div>
    @endif

    <div class="card card-soft">
        <div class="card-body p-0">
            @if($offices->isEmpty())
                <p class="text-muted mb-0 p-4">No active offices are available yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="citizen-offices-table">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Office</th>
                                <th>Municipality</th>
                                <th>Services</th>
                                <th class="text-nowrap distance-col">Distance</th>
                                <th class="pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offices as $o)
                                @php
                                    $hasCoords = $o->latitude !== null && $o->longitude !== null;
                                @endphp
                                <tr
                                    @if($hasCoords)
                                        data-office-id="{{ $o->id }}"
                                        data-lat="{{ $o->latitude }}"
                                        data-lng="{{ $o->longitude }}"
                                    @endif
                                >
                                    <td class="ps-4 fw-semibold">{{ $o->name }}</td>
                                    <td class="text-muted small">{{ $o->municipality?->name ?? '—' }}</td>
                                    <td>{{ $o->services_count }}</td>
                                    <td class="text-muted small distance-col" data-distance-cell>—</td>
                                    <td class="pe-4 text-end">
                                        <a href="{{ route('citizen.offices.show', $o) }}" class="btn btn-sm btn-primary">View services</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($offices->hasPages())
                    <div class="card-footer bg-white border-0">{{ $offices->links() }}</div>
                @endif
            @endif
        </div>
    </div>
@endsection

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
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function fmtKm(km) {
        if (km < 1) return (Math.round(km * 1000)) + ' m';
        return km.toFixed(1) + ' km';
    }

    var cfg = @json($mapConfig ?? []);
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
        var lines = '<strong>' + esc(m.name) + '</strong>';
        if (m.address) lines += '<br><span class="text-muted small">' + esc(m.address) + '</span>';
        lines += '<br><span class="small">' + m.services + ' active service(s)</span>';
        lines += '<br><a href="' + esc(m.url) + '">View services</a>';
        return lines;
    }

    rawMarkers.forEach(function (m) {
        L.marker([m.lat, m.lng]).bindPopup(popupHtml(m)).addTo(layer);
    });

    var userMarker = null;
    var userLat = null;
    var userLng = null;
    var statusEl = document.getElementById('citizen-offices-location-status');
    var sortBtn = document.getElementById('citizen-offices-sort-nearest');
    var tbody = document.querySelector('#citizen-offices-table tbody');

    function applyDistances() {
        if (userLat == null || userLng == null) return;
        document.querySelectorAll('#citizen-offices-table tbody tr[data-lat]').forEach(function (row) {
            var lat = parseFloat(row.getAttribute('data-lat'), 10);
            var lng = parseFloat(row.getAttribute('data-lng'), 10);
            var cell = row.querySelector('[data-distance-cell]');
            if (!cell || isNaN(lat) || isNaN(lng)) return;
            var km = haversineKm(userLat, userLng, lat, lng);
            cell.textContent = fmtKm(km);
            row.setAttribute('data-distance-km', String(km));
        });
    }

    function sortRowsByDistance() {
        if (!tbody || userLat == null) return;
        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function (a, b) {
            var da = parseFloat(a.getAttribute('data-distance-km'), 10);
            var db = parseFloat(b.getAttribute('data-distance-km'), 10);
            if (isNaN(da)) da = 1e9;
            if (isNaN(db)) db = 1e9;
            return da - db;
        });
        rows.forEach(function (r) { tbody.appendChild(r); });
    }

    document.getElementById('citizen-offices-use-location').addEventListener('click', function () {
        if (!navigator.geolocation) {
            if (statusEl) statusEl.textContent = 'Your browser does not support geolocation.';
            return;
        }
        if (statusEl) statusEl.textContent = 'Requesting location…';
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                if (userMarker) layer.removeLayer(userMarker);
                userMarker = L.circleMarker([userLat, userLng], {
                    radius: 10,
                    fillColor: '#4c1d95',
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).bindPopup('Your location').addTo(layer);
                try {
                    map.fitBounds(L.latLngBounds(latlngs.concat([[userLat, userLng]])).pad(0.15));
                } catch (e) { map.setView([userLat, userLng], 12); }
                if (statusEl) statusEl.textContent = 'Showing distance from your location (approximate).';
                applyDistances();
                if (sortBtn) sortBtn.classList.remove('d-none');
            },
            function () {
                if (statusEl) statusEl.textContent = 'Could not read location. Check browser permissions.';
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
        );
    });

    if (sortBtn) {
        sortBtn.addEventListener('click', function () {
            sortRowsByDistance();
        });
    }
    } // end initMap

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap);
    } else {
        initMap();
    }
})();
</script>
@endpush
@endif
