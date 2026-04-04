@php
    /** @var \Illuminate\Support\Collection<int,\App\Models\Message> $messages */
    /** @var string $portal office|citizen */
    /** @var \App\Models\GovernmentOffice $office */
    /** @var int $citizenUserId citizen user id for this thread (Echo channel) */
    $mineBubbleOffice = 'bg-success bg-opacity-10 border border-success border-opacity-25 text-dark';
    $mineBubbleCitizen = 'text-white border-0';
    $mineStyleCitizen = 'background: linear-gradient(135deg, var(--accent-mid), var(--accent));';
    $theirBubble = 'bg-white border';
@endphp

<div
    id="live-chat-scroll"
    class="rounded-3 border bg-light p-3 mb-3"
    style="min-height: 220px; max-height: 420px; overflow-y: auto;"
    data-office-id="{{ $office->id }}"
    data-citizen-user-id="{{ $citizenUserId }}"
    data-chat-portal="{{ $portal }}"
    data-current-user-id="{{ auth()->id() }}"
>
    <div id="live-chat-messages">
        @forelse($messages as $msg)
            @php
                $mine = (int) $msg->sender_id === (int) auth()->id();
                $bubbleClass = $mine
                    ? ($portal === 'office' ? $mineBubbleOffice : $mineBubbleCitizen)
                    : $theirBubble;
            @endphp
            <div
                class="d-flex mb-3 chat-message-row {{ $mine ? 'justify-content-end' : 'justify-content-start' }}"
                data-message-id="{{ $msg->id }}"
            >
                <div
                    class="rounded-3 px-3 py-2 shadow-sm chat-message-bubble {{ $bubbleClass }}"
                    style="max-width: min(92%, 28rem); {{ $mine && $portal === 'citizen' ? $mineStyleCitizen : '' }}"
                >
                    <div class="d-flex justify-content-between align-items-baseline gap-2 flex-wrap">
                        <span class="fw-semibold small chat-message-sender">{{ $msg->sender?->name ?? 'User' }}</span>
                        <time class="text-muted small chat-message-time" datetime="{{ $msg->created_at?->toIso8601String() }}" style="font-size: 0.7rem;">
                            {{ $msg->created_at?->format('M j, Y g:i a') }}
                        </time>
                    </div>
                    <div class="small mt-1 mb-0 chat-message-body" style="white-space: pre-wrap;">{{ $msg->body }}</div>
                </div>
            </div>
        @empty
            <p id="live-chat-empty" class="text-muted small text-center mb-0 py-4">No messages yet. Say hello below.</p>
        @endforelse
    </div>
</div>
