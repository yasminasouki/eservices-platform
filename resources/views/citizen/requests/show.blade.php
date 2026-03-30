@extends('layouts.citizen')

@section('title', 'Request #'.$request->id)

@php
    use Illuminate\Support\Facades\Storage;
    $statusBadge = fn (string $status) => match ($status) {
        'pending' => 'bg-warning text-dark',
        'in_review' => 'bg-info text-dark',
        'missing_documents' => 'bg-warning',
        'approved' => 'bg-primary',
        'rejected' => 'bg-danger',
        'completed' => 'bg-success',
        default => 'bg-secondary',
    };
    $statusLabel = fn (string $status) => str($status)->replace('_', ' ')->title()->toString();
@endphp

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.requests.index') }}">My requests</a></li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $request->id }}</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Request #{{ $request->id }}</h2>
            <p class="text-muted small mb-0 font-monospace">Tracking: {{ $request->qr_code }}</p>
        </div>
        <span class="badge {{ $statusBadge($request->status) }} fs-6">{{ $statusLabel($request->status) }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Service & office</div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Service</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->service?->name ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Office</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->governmentOffice?->name ?? '—' }}</dd>
                        @if($request->governmentOffice?->address)
                            <dt class="col-sm-4 text-muted">Address</dt>
                            <dd class="col-sm-8 mb-2">{{ $request->governmentOffice->address }}</dd>
                        @endif
                        <dt class="col-sm-4 text-muted">Submitted</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->submitted_at?->format('Y-m-d H:i') ?? $request->created_at?->format('Y-m-d H:i') }}</dd>
                        <dt class="col-sm-4 text-muted">Your notes</dt>
                        <dd class="col-sm-8 mb-0">{!! $request->notes ? nl2br(e($request->notes)) : '—' !!}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            @if($request->status === 'rejected' && $request->rejection_reason)
                <div class="alert alert-danger">
                    <strong>Rejected:</strong> {{ $request->rejection_reason }}
                </div>
            @endif
            @if($request->status === 'missing_documents' && $request->missing_docs_note)
                <div class="alert alert-warning">
                    <strong>Missing documents:</strong> {{ $request->missing_docs_note }}
                </div>
            @endif
        </div>
    </div>

    <div class="card card-soft">
        <div class="card-header bg-white border-0 fw-semibold">Documents</div>
        <div class="card-body p-0">
            @if($request->documents->isEmpty())
                <p class="text-muted small mb-0 p-3">No documents on file.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($request->documents as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <div>
                                <div class="fw-semibold">{{ $doc->file_name }}</div>
                                <div class="text-muted small">{{ str($doc->type)->replace('_', ' ')->title() }} · {{ $doc->uploaded_by === 'office' ? 'Office' : 'You' }}</div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Open</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
