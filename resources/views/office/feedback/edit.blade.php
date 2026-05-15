@extends('layouts.office')

@section('title', 'Reply to Feedback')

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }


    /* Green card */
    .green-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 12px rgba(21,128,61,.06);
        overflow: hidden; margin-bottom: 1.25rem;
    }
    .green-card-header {
        padding: .9rem 1.25rem; border-bottom: 1px solid #f0fdf4;
        font-weight: 700; font-size: .9rem; color: #0f2d13;
        background: #f0fdf4;
    }
    .green-card-body { padding: 1.1rem 1.25rem; }

    /* Citizen info */
    .citizen-name  { font-weight: 700; font-size: 1rem; color: #0f2d13; }
    .citizen-email { font-size: .8rem; color: #52916b; }

    /* Stars */
    .star-display { color: #f59e0b; font-size: 1.15rem; }

    /* Comment block */
    .comment-block { font-size: .9rem; color: #1a2e1c; margin-bottom: 0; margin-top: .6rem; }
    .no-comment    { font-size: .85rem; color: #52916b; font-style: italic; margin-top: .6rem; }
    .fb-meta       { font-size: .76rem; color: #52916b; margin-top: .85rem; }
    .fb-meta a     { color: #16a34a; text-decoration: none; }
    .fb-meta a:hover { text-decoration: underline; }

    /* Current reply quoted block */
    .current-reply-block {
        background: #f0fdf4; border-left: 4px solid #16a34a;
        border-radius: 0 8px 8px 0;
        padding: .75rem 1rem; margin-bottom: 1.1rem;
    }
    .current-reply-label {
        font-size: .65rem; text-transform: uppercase; font-weight: 700;
        color: #52916b; letter-spacing: .07em; margin-bottom: .4rem;
    }
    .current-reply-text { font-size: .85rem; color: #0f2d13; }
    .current-reply-meta { font-size: .74rem; color: #52916b; margin-top: .5rem; }

    /* Form labels */
    .green-card-body .form-label { font-size: .82rem; font-weight: 600; color: #14532d; }
    .green-card-body .form-control { border-color: #d1fae5; font-size: .875rem; }
    .green-card-body .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }

    /* Submit button */
    .btn-save-reply {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .875rem; padding: .5rem 1.25rem;
        border-radius: 9px; transition: background .15s; cursor: pointer;
        text-decoration: none;
    }
    .btn-save-reply:hover { background: #15803d; color: #fff; }
    .btn-cancel {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #f0fdf4; border: 1px solid #d1fae5; color: #15803d;
        font-weight: 600; font-size: .875rem; padding: .49rem 1.1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-cancel:hover { background: #dcfce7; }
</style>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <span>
            <a href="{{ route('office.feedback.index', $office) }}" class="breadcrumb-link">Feedback</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">#{{ $feedback->id }}</span>
        </span>
    </nav>

    {{-- Page header --}}
    <div class="mb-4">
        <div class="page-title">Reply to Feedback</div>
        <p class="page-sub">{{ $office->name }}</p>
    </div>

    <div class="row g-4">

        {{-- Left: citizen message --}}
        <div class="col-lg-7">
            <div class="green-card">
                <div class="green-card-header">
                    <i class="bi bi-chat-quote me-2 text-success"></i>Citizen message
                </div>
                <div class="green-card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-flex-start mb-1">
                        <div>
                            <div class="citizen-name">{{ $feedback->citizen?->name }}</div>
                            <div class="citizen-email">{{ $feedback->citizen?->email }}</div>
                        </div>
                        <div class="star-display" aria-label="{{ $feedback->rating }} of 5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                    </div>

                    @if($feedback->comment)
                        <p class="comment-block">{{ $feedback->comment }}</p>
                    @else
                        <p class="no-comment">No written comment — rating only.</p>
                    @endif

                    <div class="fb-meta">
                        @if($feedback->serviceRequest)
                            <a href="{{ route('office.requests.show', [$office, $feedback->serviceRequest]) }}">
                                Service request #{{ $feedback->serviceRequest->id }}
                            </a>
                        @else
                            General office feedback
                        @endif
                        · Submitted {{ $feedback->created_at?->format('Y-m-d H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: reply form --}}
        <div class="col-lg-5">
            <div class="green-card">
                <div class="green-card-header">
                    <i class="bi bi-reply me-2 text-success"></i>{{ $feedback->replied_at ? 'Update reply' : 'Your reply' }}
                </div>
                <div class="green-card-body">

                    @if($feedback->office_reply && $feedback->replied_at)
                        <div class="current-reply-block">
                            <div class="current-reply-label">Current reply</div>
                            <div class="current-reply-text">{!! nl2br(e($feedback->office_reply)) !!}</div>
                            <div class="current-reply-meta">
                                {{ $feedback->reply_is_public ? 'Public' : 'Private' }}
                                · {{ $feedback->replied_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('office.feedback.reply', [$office, $feedback]) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="office_reply" class="form-label">Message</label>
                            <textarea class="form-control @error('office_reply') is-invalid @enderror"
                                      name="office_reply" id="office_reply" rows="6" required maxlength="5000"
                                      placeholder="Thank them, clarify a policy, or offer next steps…">{{ old('office_reply', $feedback->office_reply) }}</textarea>
                            @error('office_reply')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="reply_is_public" value="0">
                            <input class="form-check-input" type="checkbox" name="reply_is_public" value="1" id="reply_is_public"
                                   @checked(old('reply_is_public', $feedback->reply_is_public) == 1)>
                            <label class="form-check-label" for="reply_is_public" style="font-size:.82rem;color:#374151;">
                                Public reply (visible with office listing when the citizen portal shows feedback).
                            </label>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn-save-reply">
                                <i class="bi bi-send"></i>Save reply
                            </button>
                            <a href="{{ route('office.feedback.index', $office) }}" class="btn-cancel">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
