@extends('layouts.admin')

@section('title', 'Request #'.$request->id)

@php
    use Illuminate\Support\Facades\Storage;
    $statusBadgeClass = fn (string $status) => match ($status) {
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
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="small mb-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.service-requests.index') }}">Service operations</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $request->id }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">Request #{{ $request->id }}</h2>
            <p class="text-muted small mb-0">
                {{ $request->governmentOffice?->name ?? '—' }}
                @if($request->governmentOffice?->municipality)
                    · {{ $request->governmentOffice->municipality->name }}
                @endif
            </p>
        </div>
        <span class="badge {{ $statusBadgeClass($request->status) }} fs-6">
            {{ $statusLabel($request->status) }}
        </span>
    </div>

    @if($request->submitted_at === null)
        <div class="alert alert-secondary small mb-4 mb-lg-0">
            <strong>Awaiting citizen payment.</strong> This record exists but <code>submitted_at</code> is not set — the municipality portal does not list it until the service fee is paid (or the service is free).
        </div>
    @endif

    @include('partials.service-request-public-qr', [
        'trackingUrl' => $trackingUrl,
        'trackingQrDataUri' => $trackingQrDataUri,
        'referenceCode' => $request->qr_code,
    ])

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Citizen &amp; service</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Citizen</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->citizen?->name ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->citizen?->email ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->citizen?->phone ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Service</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->service?->name ?? '—' }}</dd>
                        @if($request->service?->category)
                            <dt class="col-sm-4 text-muted">Category</dt>
                            <dd class="col-sm-8 mb-2">{{ $request->service->category->name }}</dd>
                        @endif
                        <dt class="col-sm-4 text-muted">Submitted</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->submitted_at?->format('Y-m-d H:i') ?? $request->created_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Citizen notes</dt>
                        <dd class="col-sm-8 mb-2">{!! $request->notes ? nl2br(e($request->notes)) : '—' !!}</dd>
                        @if($request->rejection_reason)
                            <dt class="col-sm-4 text-muted">Rejection reason</dt>
                            <dd class="col-sm-8 mb-2">{!! nl2br(e($request->rejection_reason)) !!}</dd>
                        @endif
                        @if($request->missing_docs_note)
                            <dt class="col-sm-4 text-muted">Missing docs</dt>
                            <dd class="col-sm-8 mb-2">{!! nl2br(e($request->missing_docs_note)) !!}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Payment</div>
                <div class="card-body small">
                    @if($request->payment)
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">Amount</dt>
                            <dd class="col-sm-8 mb-2">{{ $request->payment->currency }} {{ number_format((float) $request->payment->amount, 2) }}</dd>
                            <dt class="col-sm-4 text-muted">Method</dt>
                            <dd class="col-sm-8 mb-2">{{ str($request->payment->method ?? '—')->replace('_', ' ')->title() }}</dd>
                            <dt class="col-sm-4 text-muted">Status</dt>
                            <dd class="col-sm-8 mb-2"><span class="badge bg-light text-dark">{{ $request->payment->status }}</span></dd>
                            @if($request->payment->paid_at)
                                <dt class="col-sm-4 text-muted">Paid at</dt>
                                <dd class="col-sm-8 mb-0">{{ $request->payment->paid_at->format('Y-m-d H:i') }}</dd>
                            @endif
                        </dl>
                    @else
                        <p class="text-muted mb-0">No payment record for this request.</p>
                    @endif
                </div>
            </div>

            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">Documents</div>
                <div class="card-body p-0">
                    @if($request->documents->isEmpty())
                        <p class="text-muted small mb-0 p-3">No documents.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($request->documents as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                    <div>
                                        <div class="fw-semibold">{{ $doc->file_name }}</div>
                                        <div class="text-muted small">
                                            {{ str($doc->type)->replace('_', ' ')->title() }}
                                            · {{ $doc->uploaded_by === 'office' ? 'Office' : 'Citizen' }}
                                            @if($doc->uploader)
                                                · {{ $doc->uploader->name }}
                                            @endif
                                        </div>
                                        @if($doc->description)
                                            <div class="small mt-1">{{ $doc->description }}</div>
                                        @endif
                                    </div>
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                        <i class="bi bi-download me-1"></i>Open
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="alert alert-light border small mb-4 mb-lg-0">
                <i class="bi bi-info-circle me-1"></i>
                This view is read-only. Status changes and office uploads are handled in the municipality portal.
            </div>

            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">Status history</div>
                <div class="card-body">
                    @if($request->statusLogs->isEmpty())
                        <p class="text-muted small mb-0">No changes recorded yet.</p>
                    @else
                        <ul class="list-unstyled small mb-0">
                            @foreach($request->statusLogs as $log)
                                <li class="mb-3 pb-3 border-bottom border-light">
                                    <div class="d-flex justify-content-between gap-2 flex-wrap">
                                        <span class="text-muted">{{ $log->created_at?->format('M j, Y g:i a') }}</span>
                                        @if($log->changedBy)
                                            <span>{{ $log->changedBy->name }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-1">
                                        @if($log->from_status)
                                            <span class="badge {{ $statusBadgeClass($log->from_status) }}">{{ $statusLabel($log->from_status) }}</span>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                        @endif
                                        <span class="badge {{ $statusBadgeClass($log->to_status) }}">{{ $statusLabel($log->to_status) }}</span>
                                    </div>
                                    @if($log->notes)
                                        <div class="mt-2 text-muted">{{ $log->notes }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
