@extends('layouts.citizen')

@section('title', $serviceRequest ? __('ui.citizen_feedback_rate_service') : __('ui.citizen_feedback_rate_office'))

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            @if($serviceRequest)
                <li class="breadcrumb-item"><a href="{{ route('citizen.requests.index') }}">{{ __('ui.my_requests') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('citizen.requests.show', $serviceRequest) }}">#{{ $serviceRequest->id }}</a></li>
            @else
                <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">{{ __('ui.offices') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('citizen.offices.show', $office) }}">{{ $office->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ __('ui.citizen_feedback_title') }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">
                    {{ $serviceRequest ? __('ui.citizen_feedback_rate_service') : __('ui.citizen_feedback_rate_office') }}
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
                            <label class="form-label fw-semibold">{{ __('ui.citizen_feedback_your_rating') }}</label>
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
                            <label for="comment" class="form-label fw-semibold">{{ __('ui.citizen_feedback_comments') }} <span class="text-muted fw-normal">({{ __('ui.citizen_feedback_optional') }})</span></label>
                            <textarea class="form-control @error('comment') is-invalid @enderror" name="comment" id="comment" rows="5" maxlength="2000"
                                      placeholder="{{ __('ui.citizen_feedback_placeholder') }}">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">{{ __('ui.citizen_feedback_submit') }}</button>
                            @if($serviceRequest)
                                <a href="{{ route('citizen.requests.show', $serviceRequest) }}" class="btn btn-outline-secondary">{{ __('ui.citizen_feedback_cancel') }}</a>
                            @else
                                <a href="{{ route('citizen.offices.show', $office) }}" class="btn btn-outline-secondary">{{ __('ui.citizen_feedback_cancel') }}</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
