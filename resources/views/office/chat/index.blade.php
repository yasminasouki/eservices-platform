@extends('layouts.office')

@section('title', 'Live chat — '.$office->name)

@push('styles')
<style>
.conv-row {
    display: flex;
    align-items: center;
    gap: .9rem;
    padding: .9rem 1.1rem;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: background .12s;
}
.conv-row:last-child { border-bottom: none; }
.conv-row:hover { background: #f8fffe; }
.conv-row.has-unread { background: #f0fdf4; }
.conv-row.has-unread:hover { background: #dcfce7; }

.conv-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .95rem;
    flex-shrink: 0;
    position: relative;
}
.conv-unread-dot {
    position: absolute;
    top: 0; right: 0;
    width: 12px; height: 12px;
    background: #ef4444;
    border: 2px solid #fff;
    border-radius: 50%;
}

.conv-body { flex: 1; min-width: 0; }
.conv-name {
    font-size: .9rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.conv-row.has-unread .conv-name { font-weight: 700; }
.conv-preview {
    font-size: .8rem;
    color: #6b7280;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-top: 1px;
}
.conv-row.has-unread .conv-preview { color: #374151; font-weight: 500; }

.conv-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .3rem;
    flex-shrink: 0;
}
.conv-time {
    font-size: .7rem;
    color: #9ca3af;
    white-space: nowrap;
}
.conv-badge {
    background: #ef4444;
    color: #fff;
    border-radius: 99px;
    font-size: .68rem;
    font-weight: 700;
    padding: .1em .55em;
    min-width: 1.4em;
    text-align: center;
    line-height: 1.5;
}

/* ── Empty state ── */
.chat-empty-state {
    padding: 3.5rem 1.5rem;
    text-align: center;
}
.chat-empty-icon-lg {
    width: 64px; height: 64px;
    background: #f0fdf4;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.1rem;
}
</style>
@endpush

@section('content')
    <nav aria-label="breadcrumb" class="small mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('office.dashboard') }}">{{ __('ui.dashboard') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('ui.office_chat_title') }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-chat-dots me-2"></i>{{ __('ui.office_chat_title') }}</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        @php $totalUnread = collect($threads)->sum(fn($t) => (int)($t['unread_for_office'] ?? 0)); @endphp
        @if($totalUnread > 0)
            <span class="badge bg-danger rounded-pill" style="font-size:.8rem;">{{ $totalUnread }} {{ __('ui.office_chat_unread') }}</span>
        @endif
    </div>

    <div class="card card-soft overflow-hidden">
        <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3 px-4">
            <span class="fw-semibold" style="font-size:.9rem;">{{ __('ui.office_chat_conversations') }}</span>
            <span class="text-muted small">{{ __('ui.office_chat_citizens_count', ['count' => count($threads)]) }}</span>
        </div>

        @if(empty($threads))
            <div class="chat-empty-state">
                <div class="chat-empty-icon-lg">
                    <i class="bi bi-chat-square-dots" style="font-size:1.6rem; color:#34d399;"></i>
                </div>
                <p class="fw-semibold mb-1" style="color:#065f46;">{{ __('ui.office_chat_no_convos') }}</p>
                <p class="text-muted small mb-0">{{ __('ui.office_chat_no_convos_desc') }}</p>
            </div>
        @else
            @php
                $palette = ['#e9d5ff','#dbeafe','#d1fae5','#fef9c3','#fee2e2','#e0f2fe','#fce7f3'];
                $textColors = ['#5b21b6','#1e40af','#065f46','#92400e','#991b1b','#0c4a6e','#831843'];
            @endphp
            @foreach($threads as $t)
                @php
                    /** @var \App\Models\User $c */
                    $c      = $t['citizen'];
                    $last   = $t['last_message'];
                    $unread = (int) ($t['unread_for_office'] ?? 0);
                    $init   = strtoupper(substr($c->name, 0, 1));
                    $ci     = (ord($init) - 65 + count($palette)) % count($palette);
                    $bgCol  = $palette[$ci];
                    $txtCol = $textColors[$ci];
                @endphp
                <a
                    href="{{ route('office.chat.show', [$office, $c]) }}"
                    class="conv-row {{ $unread > 0 ? 'has-unread' : '' }}"
                >
                    <div class="conv-avatar" style="background:{{ $bgCol }}; color:{{ $txtCol }};">
                        {{ $init }}
                        @if($unread > 0)
                            <span class="conv-unread-dot"></span>
                        @endif
                    </div>

                    <div class="conv-body">
                        <div class="conv-name">{{ $c->name }}</div>
                        @if($last)
                            <div class="conv-preview">{{ \Illuminate\Support\Str::limit($last->body, 80) }}</div>
                        @else
                            <div class="conv-preview text-muted fst-italic">{{ __('ui.office_chat_no_messages') }}</div>
                        @endif
                    </div>

                    <div class="conv-meta">
                        @if($last)
                            <span class="conv-time">{{ $last->created_at?->diffForHumans() }}</span>
                        @endif
                        @if($unread > 0)
                            <span class="conv-badge">{{ $unread }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        @endif
    </div>
@endsection
