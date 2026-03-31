@extends('layouts.office')

@section('title', 'Reply to feedback')

@section('content')
    <nav aria-label="breadcrumb" class="small mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
                <a href="{{ route('office.feedback.index', $office) }}">Feedback</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $feedback->id }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Reply to feedback</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Citizen message</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <div>
                            <div class="fw-semibold">{{ $feedback->citizen?->name }}</div>
                            <div class="text-muted small">{{ $feedback->citizen?->email }}</div>
                        </div>
                        <div class="text-warning" aria-label="{{ $feedback->rating }} of 5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                    </div>
                    @if($feedback->comment)
                        <p class="mb-0">{{ $feedback->comment }}</p>
                    @else
                        <p class="text-muted mb-0">No written comment — rating only.</p>
                    @endif
                    <div class="small text-muted mt-3">
                        @if($feedback->serviceRequest)
                            <a href="{{ route('office.requests.show', [$office, $feedback->serviceRequest]) }}">Service request #{{ $feedback->serviceRequest->id }}</a>
                        @else
                            General office feedback
                        @endif
                        · Submitted {{ $feedback->created_at?->format('Y-m-d H:i') }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">
                    {{ $feedback->replied_at ? 'Update reply' : 'Your reply' }}
                </div>
                <div class="card-body">
                    @if($feedback->office_reply && $feedback->replied_at)
                        <div class="border-start border-4 border-success ps-2 mb-3 small text-muted">
                            <div class="text-uppercase fw-semibold mb-1" style="font-size: 0.65rem;">Current reply</div>
                            <div class="text-dark">{!! nl2br(e($feedback->office_reply)) !!}</div>
                            <div class="mt-1">{{ $feedback->reply_is_public ? 'Public' : 'Private' }} · {{ $feedback->replied_at->format('Y-m-d H:i') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('office.feedback.reply', [$office, $feedback]) }}">
                        @csrf
                        @method('PATCH')
                        <label for="office_reply" class="form-label small fw-semibold">Message</label>
                        <textarea class="form-control @error('office_reply') is-invalid @enderror"
                                  name="office_reply" id="office_reply" rows="6" required maxlength="5000"
                                  placeholder="Thank them, clarify a policy, or offer next steps…">{{ old('office_reply', $feedback->office_reply) }}</textarea>
                        @error('office_reply')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="form-check form-switch mt-3 mb-3">
                            <input type="hidden" name="reply_is_public" value="0">
                            <input class="form-check-input" type="checkbox" name="reply_is_public" value="1" id="reply_is_public"
                                   @checked(old('reply_is_public', $feedback->reply_is_public) == 1)>
                            <label class="form-check-label small" for="reply_is_public">
                                Public reply (visible with office listing when the citizen portal shows feedback).
                            </label>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Save reply
                            </button>
                            <a href="{{ route('office.feedback.index', $office) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
