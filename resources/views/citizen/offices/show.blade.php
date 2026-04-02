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
            @if($avgRating !== null)
                @php $avgStars = (int) round($avgRating); @endphp
                <p class="small mb-0 mt-2">
                    <span class="text-warning" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $avgStars ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </span>
                    <span class="text-muted ms-1">{{ number_format($avgRating, 1) }} average from visitor feedback</span>
                </p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('citizen.appointments.book', $office) }}" class="btn btn-success btn-sm">
                <i class="bi bi-calendar-plus me-1"></i>Book Appointment
            </a>
            <a href="{{ route('citizen.feedback.office.create', $office) }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-star me-1"></i>Leave feedback
            </a>
            <a href="{{ route('citizen.offices.index') }}" class="btn btn-outline-secondary btn-sm">All offices</a>
        </div>
    </div>

    @if($publicReviews->isNotEmpty())
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold">Recent visitor feedback</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach($publicReviews as $rev)
                        <li class="border-bottom pb-3 mb-3">
                            <div class="text-warning small mb-1" aria-label="{{ $rev->rating }} of 5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $rev->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                            @if($rev->comment)
                                <p class="small mb-2">{{ $rev->comment }}</p>
                            @endif
                            @if($rev->office_reply && $rev->reply_is_public)
                                <div class="small border-start border-3 border-success ps-2 text-muted">
                                    <strong class="text-body">Office:</strong> {{ $rev->office_reply }}
                                </div>
                            @elseif($rev->office_reply && ! $rev->reply_is_public)
                                @if(auth()->id() === $rev->user_id)
                                    <div class="small border-start border-3 border-secondary ps-2 text-muted">
                                        <strong class="text-body">Office (private reply to you):</strong> {{ $rev->office_reply }}
                                    </div>
                                @else
                                    <p class="small text-muted fst-italic mb-0">The office sent a private reply to this visitor.</p>
                                @endif
                            @endif
                            <div class="text-muted" style="font-size: 0.75rem;">{{ $rev->created_at?->format('M j, Y') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

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

    @if(isset($availableSlots) && $availableSlots->isNotEmpty())
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold">
                <i class="bi bi-calendar-check me-2 text-success"></i>Available Appointments
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($availableSlots as $slot)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($slot->date)->format('l, d M Y') }}</div>
                                <div class="text-muted small">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('citizen.appointments.store', [$office, $slot]) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-calendar-plus me-1"></i>Book
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
