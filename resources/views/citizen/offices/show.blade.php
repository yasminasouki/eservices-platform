@extends('layouts.citizen')

@section('title', $office->name)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">Offices</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $office->name }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $office->name }}</h2>
            @if($office->address)
                <p class="text-muted small mb-0"><i class="bi bi-geo-alt me-1"></i>{{ $office->address }}</p>
            @endif
        </div>
        <a href="{{ route('citizen.offices.index') }}" class="btn btn-outline-secondary btn-sm">All offices</a>
    </div>

    @if($categories->isEmpty())
        <div class="card card-soft">
            <div class="card-body text-muted">This office has not published any services yet.</div>
        </div>
    @else
        @foreach($categories as $category)
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">{{ $category->name }}</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($category->services as $svc)
                            <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $svc->name }}</div>
                                    @if($svc->description)
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($svc->description, 120) }}</div>
                                    @endif
                                    <div class="small mt-1">
                                        <span class="text-muted">Fee:</span>
                                        {{ number_format((float) $svc->price, 2) }}
                                    </div>
                                </div>
                                <a href="{{ route('citizen.services.apply', [$office, $svc]) }}" class="btn btn-sm btn-primary flex-shrink-0">
                                    Request
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    @endif
@endsection
