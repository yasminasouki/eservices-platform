@extends('layouts.office')

@section('title', 'Live chat — '.$office->name)

@section('content')
    <nav aria-label="breadcrumb" class="small mb-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('office.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Live chat</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-chat-dots me-2"></i>Live chat</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0 fw-semibold">Conversations</div>
        <div class="card-body p-0">
            @if($threads->isEmpty())
                <p class="text-muted small mb-0 p-4">No messages yet. Citizens can start a chat from your office’s public page.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($threads as $t)
                        @php
                            /** @var \App\Models\User $c */
                            $c = $t['citizen'];
                            $last = $t['last_message'];
                            $unread = (int) ($t['unread_for_office'] ?? 0);
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-semibold">{{ $c->name }}</div>
                                @if($last)
                                    <div class="text-muted small text-truncate" style="max-width: 28rem;">
                                        {{ \Illuminate\Support\Str::limit($last->body, 120) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.72rem;">
                                        {{ $last->created_at?->diffForHumans() }}
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @if($unread > 0)
                                    <span class="badge bg-danger rounded-pill">{{ $unread }}</span>
                                @endif
                                <a href="{{ route('office.chat.show', [$office, $c]) }}" class="btn btn-success btn-sm">
                                    Open
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
