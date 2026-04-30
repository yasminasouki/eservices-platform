{{-- Leaflet is bundled via Vite (resources/js/app.js). This partial only adds map styles. --}}
@push('styles')
    <style>
        .leaflet-map-shell { border-radius: 12px; overflow: hidden; border: 1px solid rgba(0,0,0,.08); }
        .leaflet-map { height: min(420px, 55vh); width: 100%; z-index: 0; }
        .leaflet-map.leaflet-map-sm { height: 280px; }
    </style>
@endpush
