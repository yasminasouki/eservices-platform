@php
    $nav = [
        ['route' => 'office.dashboard', 'label' => 'Dashboard', 'icon' => 'speedometer2'],
    ];
@endphp
<nav class="office-subnav bg-white border-bottom shadow-sm">
    <div class="container py-2">
        <ul class="nav nav-pills flex-column flex-md-row flex-wrap gap-1 gap-md-2 small mb-0">
            @foreach($nav as $item)
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}"
                       class="nav-link py-2 px-3 {{ request()->routeIs($item['route']) ? 'active' : 'text-dark' }}"
                       @if(request()->routeIs($item['route'])) aria-current="page" @endif>
                        <i class="bi bi-{{ $item['icon'] }} me-1"></i>{{ $item['label'] }}
                    </a>
                </li>
            @endforeach
            <li class="nav-item">
                <span class="nav-link py-2 px-3 text-muted" title="Added in upcoming steps">
                    <i class="bi bi-building me-1"></i>Office profile
                </span>
            </li>
            <li class="nav-item">
                <span class="nav-link py-2 px-3 text-muted" title="Added in upcoming steps">
                    <i class="bi bi-grid me-1"></i>Services
                </span>
            </li>
            <li class="nav-item">
                <span class="nav-link py-2 px-3 text-muted" title="Added in upcoming steps">
                    <i class="bi bi-inbox me-1"></i>Requests
                </span>
            </li>
        </ul>
    </div>
</nav>
