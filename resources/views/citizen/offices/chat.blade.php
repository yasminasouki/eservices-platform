@extends('layouts.citizen')

@section('title', 'Chat — '.$office->name)

@push('styles')
<style>
/* ── Compose area ── */
.chat-compose-wrap {
    background: #fff;
    border: 1.5px solid #e0d9ff;
    border-radius: 14px;
    padding: .55rem .55rem .55rem .85rem;
    display: flex;
    align-items: flex-end;
    gap: .6rem;
    transition: border-color .15s, box-shadow .15s;
}
.chat-compose-wrap:focus-within {
    border-color: var(--accent-light);
    box-shadow: 0 0 0 3px var(--accent-subtle);
}
.chat-compose-input {
    flex: 1;
    border: none !important;
    box-shadow: none !important;
    resize: none;
    min-height: 38px;
    max-height: 130px;
    overflow-y: auto;
    font-size: .9rem;
    padding: .35rem 0;
    background: transparent;
    line-height: 1.5;
}
.chat-compose-input:focus { outline: none; }
.chat-send-btn {
    width: 38px; height: 38px;
    padding: 0;
    flex-shrink: 0;
    border-radius: 10px !important;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    background: linear-gradient(135deg, var(--accent-mid), var(--accent));
    border: none;
    color: #fff;
    transition: opacity .15s, transform .1s;
}
.chat-send-btn:hover { opacity: .88; color: #fff; }
.chat-send-btn:active { transform: scale(.93); }
.chat-send-btn:disabled { opacity: .45; }

/* ── Office info header ── */
.chat-office-header {
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .85rem 1.1rem;
    background: linear-gradient(135deg, #f5f3ff, #faf9ff);
    border-bottom: 1px solid #ede9fe;
    border-radius: 14px 14px 0 0;
}
.chat-office-avatar {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, var(--accent-mid), var(--accent));
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.chat-online-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #22c55e;
    margin-right: 4px;
    box-shadow: 0 0 0 2px #fff;
}
</style>
@endpush

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
            <p class="text-muted small mb-0">Chat directly with office staff</p>
        </div>
        <a href="{{ route('citizen.offices.show', $office) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Office details
        </a>
    </div>

    <div class="card card-soft overflow-hidden">
        {{-- Office header --}}
        <div class="chat-office-header">
            <div class="chat-office-avatar">
                <i class="bi bi-building"></i>
            </div>
            <div class="min-w-0">
                <div class="fw-bold" style="font-size:.95rem; color:#1f1235;">{{ $office->name }}</div>
                <div style="font-size:.78rem; color:#9d7ecf;">
                    <span class="chat-online-dot"></span>Office staff
                </div>
            </div>
        </div>

        <div class="card-body pt-3">
            @include('partials.office-chat-thread', [
                'messages'      => $messages,
                'portal'        => 'citizen',
                'office'        => $office,
                'citizenUserId' => auth()->id(),
            ])

            <div id="live-chat-ajax-error" class="alert alert-danger d-none small mb-2" role="alert"></div>

            <form method="POST" action="{{ route('citizen.offices.chat.store', $office) }}" data-office-chat-ajax>
                @csrf
                <div class="chat-compose-wrap">
                    <textarea
                        id="body"
                        name="body"
                        class="form-control chat-compose-input @error('body') is-invalid @enderror"
                        rows="1"
                        maxlength="5000"
                        required
                        placeholder="Ask a question or message the office…"
                    >{{ old('body') }}</textarea>
                    <button type="submit" class="btn chat-send-btn" title="Send (Ctrl+Enter)">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                @error('body')
                    <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                @enderror
                <div class="text-end mt-1" style="font-size:.67rem; color:#b4a0d4;">
                    <kbd style="background:#f3f0ff;border:1px solid #e0d9ff;color:#7c3aed;font-size:.65rem;border-radius:4px;padding:1px 4px;">Enter</kbd>
                    to send &nbsp;·&nbsp;
                    <kbd style="background:#f3f0ff;border:1px solid #e0d9ff;color:#7c3aed;font-size:.65rem;border-radius:4px;padding:1px 4px;">Shift</kbd>
                    +
                    <kbd style="background:#f3f0ff;border:1px solid #e0d9ff;color:#7c3aed;font-size:.65rem;border-radius:4px;padding:1px 4px;">Enter</kbd>
                    for new line
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
