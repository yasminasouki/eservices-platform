@extends('layouts.citizen')

@section('title', $serviceRequest ? 'Rate your request' : 'Rate this office')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            @if($serviceRequest)
                <li class="breadcrumb-item"><a href="{{ route('citizen.requests.index') }}">My requests</a></li>
                <li class="breadcrumb-item"><a href="{{ route('citizen.requests.show', $serviceRequest) }}">#{{ $serviceRequest->id }}</a></li>
            @else
                <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">Offices</a></li>
                <li class="breadcrumb-item"><a href="{{ route('citizen.offices.show', $office) }}">{{ $office->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">Feedback</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">
                    {{ $serviceRequest ? 'Rate this service' : 'Rate this office' }}
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-4">
                        {{ $office->name }}
                        @if($serviceRequest && $serviceRequest->service)
                            — {{ $serviceRequest->service->name }}
                        @endif
                    </p>

                    <form method="POST" action="{{ $serviceRequest ? route('citizen.feedback.request.store', $serviceRequest) : route('citizen.feedback.office.store', $office) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Your rating</label>
                            <div class="btn-group flex-wrap" role="group" aria-label="Star rating from 1 to 5">
                                @foreach ([5, 4, 3, 2, 1] as $rNum)
                                    <input type="radio" class="btn-check" name="rating" id="rating-{{ $rNum }}" value="{{ $rNum }}" required
                                           @checked((string) old('rating') === (string) $rNum)>
                                    <label class="btn btn-outline-warning" for="rating-{{ $rNum }}">{{ $rNum }} <i class="bi bi-star-fill"></i></label>
                                @endforeach
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="comment" class="form-label fw-semibold">Comments <span class="text-muted fw-normal">(optional)</span></label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" id="comment" rows="5" maxlength="2000"
                                      placeholder="What went well? What could be improved?">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Submit feedback</button>
                            @if($serviceRequest)
                                <a href="{{ route('citizen.requests.show', $serviceRequest) }}" class="btn btn-outline-secondary">Cancel</a>
                            @else
                                <a href="{{ route('citizen.offices.show', $office) }}" class="btn btn-outline-secondary">Cancel</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
