<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default map center (Lebanon / Beirut area) — used when no coordinates exist.
    | OpenStreetMap + Leaflet (no Google Maps API key or billing).
    |--------------------------------------------------------------------------
    */
    'default_center' => [
        'lat' => (float) env('MAP_DEFAULT_LAT', 33.8938),
        'lng' => (float) env('MAP_DEFAULT_LNG', 35.5018),
    ],

    'max_zoom' => (int) env('MAP_MAX_ZOOM', 18),

    /*
    | Low-traffic dev: https://tile.openstreetmap.org is fine.
    | Production: use your own tile server or a provider per OSM tile policy.
    */
    'tile_url' => env('OSM_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),

    'tile_attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright" rel="noopener">OpenStreetMap</a>',

    /*
    | Browser-side Nominatim search (OpenStreetMap). Be respectful: debounce, no bulk scraping.
    */
    'nominatim_endpoint' => rtrim(env('NOMINATIM_SEARCH_URL', 'https://nominatim.openstreetmap.org/search'), '/'),

];
