{{-- Leaflet + OSM tiles (free). Include once per page that needs a map. --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        .leaflet-map-shell { border-radius: 12px; overflow: hidden; border: 1px solid rgba(0,0,0,.08); }
        .leaflet-map { height: min(420px, 55vh); width: 100%; z-index: 0; }
        .leaflet-map.leaflet-map-sm { height: 280px; }
    </style>
@endpush
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endpush
