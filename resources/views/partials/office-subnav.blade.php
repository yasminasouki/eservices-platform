@php
    $ctx = $officeContext ?? null;
@endphp
<nav class="office-subnav bg-white border-bottom shadow-sm">
    <div class="container py-2">
        <ul class="nav nav-pills flex-column flex-md-row flex-wrap gap-1 gap-md-2 small mb-0">
            <li class="nav-item">
                <a href="{{ route('office.dashboard') }}"
                   class="nav-link py-2 px-3 {{ request()->routeIs('office.dashboard') ? 'active' : 'text-dark' }}"
                   @if(request()->routeIs('office.dashboard')) aria-current="page" @endif>
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('office.profile.index') }}"
                   class="nav-link py-2 px-3 {{ request()->routeIs('office.profile.*') ? 'active' : 'text-dark' }}">
                    <i class="bi bi-building me-1"></i>Office profile
                </a>
            </li>
            @if($ctx)
                <li class="nav-item">
                    <a href="{{ route('office.categories.index', $ctx) }}"
                       class="nav-link py-2 px-3 {{ request()->routeIs('office.categories.*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('office.services.index', $ctx) }}"
                       class="nav-link py-2 px-3 {{ request()->routeIs('office.services.*') ? 'active' : 'text-dark' }}">
                        <i class="bi bi-box-seam me-1"></i>Services
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <span class="nav-link py-2 px-3 text-muted" title="Assign an office to your account first">
                        <i class="bi bi-grid me-1"></i>Categories
                    </span>
                </li>
                <li class="nav-item">
                    <span class="nav-link py-2 px-3 text-muted" title="Assign an office to your account first">
                        <i class="bi bi-box-seam me-1"></i>Services
                    </span>
                </li>
            @endif
            <li class="nav-item">
                <span class="nav-link py-2 px-3 text-muted" title="Next: request inbox">
                    <i class="bi bi-inbox me-1"></i>Requests
                </span>
            </li>
        </ul>
    </div>
</nav>
