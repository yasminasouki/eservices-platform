@php
    /** @var \Illuminate\Support\Collection<int,\App\Models\Message> $messages */
    /** @var string $portal office|citizen */
    /** @var \App\Models\GovernmentOffice $office */
    /** @var int $citizenUserId citizen user id for this thread (Echo channel) */
    $mineBubbleOffice  = 'bg-success bg-opacity-10 border border-success border-opacity-25 text-dark';
    $mineBubbleCitizen = 'text-white border-0';
    $mineStyleCitizen  = 'background: linear-gradient(135deg, var(--accent-mid), var(--accent));';
    $theirBubble       = 'bg-white border';
    $avatarPalette     = ['#e9d5ff','#dbeafe','#d1fae5','#fef9c3','#fee2e2','#e0f2fe','#fce7f3'];
@endphp

<style>
/* ── Chat scroll area ── */
#live-chat-scroll {
    scrollbar-width: thin;
    scrollbar-color: #e0d9ff transparent;
}
#live-chat-scroll::-webkit-scrollbar { width: 5px; }
#live-chat-scroll::-webkit-scrollbar-track { background: transparent; }
#live-chat-scroll::-webkit-scrollbar-thumb { background: #e0d9ff; border-radius: 10px; }

/* ── Avatars ── */
.chat-avatar {
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700;
    flex-shrink: 0;
    user-select: none;
}

/* ── Bubbles ── */
.chat-message-bubble {
    transition: box-shadow .15s;
    word-break: break-word;
}
.chat-message-bubble:hover { box-shadow: 0 3px 12px rgba(0,0,0,.1) !important; }

.chat-message-time {
    font-size: 0.62rem;
    margin-top: 4px;
    opacity: .7;
    letter-spacing: .01em;
}

/* ── Date divider ── */
.chat-date-divider {
    display: flex; align-items: center; gap: .75rem;
    margin: 1.1rem 0 .6rem;
}
.chat-date-divider::before,
.chat-date-divider::after {
    content: ''; flex: 1; height: 1px; background: #e5e7eb;
}
.chat-date-divider span {
    font-size: .68rem; color: #9ca3af; font-weight: 600;
    white-space: nowrap; text-transform: uppercase; letter-spacing: .04em;
}

/* ── Empty state ── */
#live-chat-empty { padding: 2.5rem 1rem; }
.chat-empty-icon {
    width: 54px; height: 54px;
    background: #f3f0ff; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto .85rem;
}
</style>

<div
    id="live-chat-scroll"
    class="rounded-3 border p-3 mb-3"
    style="min-height: 280px; max-height: 520px; overflow-y: auto; background: #f9f8ff;"
    data-office-id="{{ $office->id }}"
    data-citizen-user-id="{{ $citizenUserId }}"
    data-chat-portal="{{ $portal }}"
    data-current-user-id="{{ auth()->id() }}"
>
    <div id="live-chat-messages">
        @forelse($messages as $msg)
            @php
                $mine       = (int) $msg->sender_id === (int) auth()->id();
                $bubbleCls  = $mine ? ($portal === 'office' ? $mineBubbleOffice : $mineBubbleCitizen) : $theirBubble;
                $senderName = $msg->sender?->name ?? 'User';
                $initial    = strtoupper(substr($senderName, 0, 1));
                $colorIdx   = (ord($initial) - 65 + count($avatarPalette)) % count($avatarPalette);
                $avatarBg   = $avatarPalette[$colorIdx];
            @endphp
            <div
                class="d-flex mb-2 chat-message-row align-items-end gap-2 {{ $mine ? 'justify-content-end' : 'justify-content-start' }}"
                data-message-id="{{ $msg->id }}"
            >
                @if(!$mine)
                    <div class="chat-avatar" style="background:{{ $avatarBg }}; color:#374151;" title="{{ $senderName }}">{{ $initial }}</div>
                @endif

                <div
                    class="rounded-3 px-3 py-2 shadow-sm chat-message-bubble {{ $bubbleCls }}"
                    style="max-width: min(78%, 28rem); {{ $mine && $portal === 'citizen' ? $mineStyleCitizen : '' }}"
                >
                    <div class="small mb-0 chat-message-body" style="white-space: pre-wrap; line-height: 1.5;">{{ $msg->body }}</div>
                    <div class="chat-message-time {{ $mine ? 'text-end' : '' }} {{ $mine && $portal === 'citizen' ? '' : 'text-muted' }}"
                         style="{{ $mine && $portal === 'citizen' ? 'color:rgba(255,255,255,.75);' : '' }}">
                        <time datetime="{{ $msg->created_at?->toIso8601String() }}">{{ $msg->created_at?->format('g:i a') }}</time>
                        @if($mine)
                            <i class="bi bi-check2 ms-1" style="font-size:.6rem;"></i>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div id="live-chat-empty" class="text-center">
                <div class="chat-empty-icon">
                    <i class="bi bi-chat-dots" style="font-size:1.4rem; color:#c4b5fd;"></i>
                </div>
                <p class="text-muted small mb-0">No messages yet. Start the conversation below.</p>
            </div>
        @endforelse
    </div>
</div>
