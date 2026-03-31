@extends('layouts.office')

@section('title', 'Citizen feedback')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Citizen feedback</h2>
            <p class="text-muted small mb-0">
                {{ $office->name }} — ratings and comments. Choose an item to write or update a public or private reply.
            </p>
        </div>
        <a href="{{ route('office.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
        </a>
    </div>

    <div class="card card-soft mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('office.feedback.index', $office) }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label for="replied" class="form-label small text-muted mb-1">Reply status</label>
                    <select name="replied" id="replied" class="form-select">
                        <option value="all" @selected($repliedFilter === 'all')>All</option>
                        <option value="no" @selected($repliedFilter === 'no')>Awaiting reply</option>
                        <option value="yes" @selected($repliedFilter === 'yes')>Replied</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </form>
        </div>
    </div>

    @if($entries->isEmpty())
        <div class="card card-soft">
            <div class="card-body text-muted text-center py-5">
                <i class="bi bi-chat-square-text display-6 d-block mb-2 opacity-50"></i>
                No feedback for this office yet.
            </div>
        </div>
    @else
        <div class="card card-soft">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Citizen</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Linked</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $fb)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $fb->citizen?->name ?? '—' }}</div>
                                    <div class="text-muted">{{ $fb->citizen?->email }}</div>
                                </td>
                                <td>
                                    <span class="text-warning" aria-label="{{ $fb->rating }} of 5 stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $fb->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </span>
                                </td>
                                <td class="text-break" style="max-width: 14rem;">
                                    {{ \Illuminate\Support\Str::limit($fb->comment ?? '—', 80) }}
                                </td>
                                <td>
                                    @if($fb->serviceRequest)
                                        <a href="{{ route('office.requests.show', [$office, $fb->serviceRequest]) }}">
                                            #{{ $fb->serviceRequest->id }}
                                        </a>
                                    @else
                                        <span class="text-muted">General</span>
                                    @endif
                                </td>
                                <td>
                                    @if($fb->replied_at)
                                        <span class="badge bg-success">Replied</span>
                                    @else
                                        <span class="badge bg-secondary">Open</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('office.feedback.edit', [$office, $fb]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-reply me-1"></i>Reply
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    @endif
@endsection
