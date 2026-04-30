@extends('layouts.citizen')

@section('title', 'Chat — '.$office->name)

@section('content')
    <nav aria-label="breadcrumb" class="small mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">Offices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.show', $office) }}">{{ $office->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Live chat</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-chat-dots me-2"></i>Live chat</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        <a href="{{ route('citizen.offices.show', $office) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Office details
        </a>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            @include('partials.office-chat-thread', [
                'messages' => $messages,
                'portal' => 'citizen',
                'office' => $office,
                'citizenUserId' => auth()->id(),
            ])

            <div id="live-chat-ajax-error" class="alert alert-danger d-none small" role="alert"></div>

            <form method="POST" action="{{ route('citizen.offices.chat.store', $office) }}" class="mt-2" data-office-chat-ajax>
                @csrf
                <label for="body" class="form-label small fw-semibold">Your message</label>
                <textarea
                    id="body"
                    name="body"
                    class="form-control @error('body') is-invalid @enderror"
                    rows="4"
                    maxlength="5000"
                    required
                    placeholder="Ask a question or message the office…"
                >{{ old('body') }}</textarea>
                @error('body')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-fill me-1"></i>Send
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var el = document.getElementById('live-chat-scroll');
            if (el) el.scrollTop = el.scrollHeight;
        })();
    </script>
@endpush
