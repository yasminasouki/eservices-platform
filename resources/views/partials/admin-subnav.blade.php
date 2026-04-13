@php
    $nav = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'speedometer2'],
        ['route' => 'admin.municipalities.index', 'label' => 'Municipalities', 'icon' => 'geo-alt', 'match' => 'admin.municipalities.*'],
        ['route' => 'admin.offices.index', 'label' => 'Offices', 'icon' => 'building', 'match' => 'admin.offices.*'],
        ['route' => 'admin.office-users.index', 'label' => 'Municipality users', 'icon' => 'person-gear', 'match' => 'admin.office-users.*'],
        ['route' => 'admin.citizens.index', 'label' => 'Citizens', 'icon' => 'people', 'match' => 'admin.citizens.*'],
        ['route' => 'admin.service-requests.index', 'label' => 'Service operations', 'icon' => 'clipboard-data', 'match' => 'admin.service-requests.*'],
        ['route' => 'admin.reports.index', 'label' => 'Reports', 'icon' => 'graph-up-arrow'],
    ];
@endphp
<nav class="admin-subnav bg-white border-bottom shadow-sm">
    <div class="container py-2">
        <ul class="nav nav-pills flex-column flex-md-row flex-wrap gap-1 gap-md-2 small mb-0">
            @foreach($nav as $item)
                @php
                    $active = isset($item['match'])
                        ? request()->routeIs($item['match'])
                        : request()->routeIs($item['route']);
                @endphp
                <li class="nav-item">
                    <a href="{{ route($item['route']) }}"
                       class="nav-link py-2 px-3 {{ $active ? 'active' : 'text-dark' }}"
                       @if($active) aria-current="page" @endif>
                        <i class="bi bi-{{ $item['icon'] }} me-1"></i>{{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
