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

    @if(!empty($awaitingPayment))
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <strong>Payment required.</strong> This service has a fee of <strong>${{ number_format((float) ($request->service?->price ?? 0), 2) }} USD</strong>.
                The office will not process your request until payment is completed.
            </div>
            <a href="{{ route('citizen.requests.pay', $request) }}" class="btn btn-warning text-dark fw-semibold">Pay now</a>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Request #{{ $request->id }}</h2>
            <p class="text-muted small mb-0">Save or print the QR below to check status anytime without logging in.</p>
        </div>
        <span class="badge {{ $statusBadge($request->status) }} fs-6">{{ $statusLabel($request->status) }}</span>
    </div>

    @include('partials.service-request-public-qr', [
        'trackingUrl' => $trackingUrl,
        'trackingQrDataUri' => $trackingQrDataUri,
        'referenceCode' => $request->qr_code,
    ])

    @if($request->governmentOffice && empty($awaitingPayment))
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>Live chat with the office</span>
                <a href="{{ route('citizen.offices.chat', $request->governmentOffice) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-chat-dots me-1"></i>Open live chat
                </a>
            </div>
            <div class="card-body small text-muted py-3 mb-0">
                Message {{ $request->governmentOffice->name }} directly (not limited to this request).
            </div>
        </div>
    @endif

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

    @if($request->payment)
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold">Payment</div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Amount</dt>
                    <dd class="col-sm-8 mb-2">${{ number_format((float) $request->payment->amount, 2) }} USD</dd>
                    <dt class="col-sm-4 text-muted">Status</dt>
                    <dd class="col-sm-8 mb-2">
                        @if($request->payment->status === 'completed')
                            <span class="badge bg-success">Paid</span>
                            @if($request->payment->paid_at)
                                <span class="text-muted ms-1">on {{ $request->payment->paid_at->format('M j, Y H:i') }}</span>
                            @endif
                        @elseif($request->payment->status === 'pending')
                            <span class="badge bg-warning text-dark">Awaiting payment</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($request->payment->status) }}</span>
                        @endif
                    </dd>
                    @if($request->payment->method && $request->payment->method !== 'pending')
                        <dt class="col-sm-4 text-muted">Method</dt>
                        <dd class="col-sm-8 mb-2">{{ ucfirst($request->payment->method) }}</dd>
                    @endif
                    @if($request->payment->transaction_id)
                        <dt class="col-sm-4 text-muted">Transaction</dt>
                        <dd class="col-sm-8 mb-0 font-monospace text-break" style="font-size:0.8rem;">{{ $request->payment->transaction_id }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    @endif

    @if($request->status === 'completed')
        <div class="card card-soft mb-4">
            <div class="card-header bg-white border-0 fw-semibold">Your feedback</div>
            <div class="card-body small">
                @if($request->feedback)
                    <div class="text-warning mb-2" aria-label="Your rating">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi {{ $i <= $request->feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                    </div>
                    @if($request->feedback->comment)
                        <p class="mb-2">{{ $request->feedback->comment }}</p>
                    @endif
                    @if($request->feedback->office_reply)
                        <div class="border-start border-4 border-success ps-3 py-2 bg-light rounded-end">
                            <div class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Office reply</div>
                            <div class="mb-0">{!! nl2br(e($request->feedback->office_reply)) !!}</div>
                            @if($request->feedback->replied_at)
                                <div class="text-muted mt-1">{{ $request->feedback->replied_at->format('Y-m-d H:i') }}</div>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0">The office has not replied yet.</p>
                    @endif
                @else
                    <p class="mb-3">Tell us how this service went — it helps municipalities improve.</p>
                    <a href="{{ route('citizen.feedback.request.create', $request) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-star me-1"></i>Rate this request
                    </a>
                @endif
            </div>
        </div>
    @endif

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
                                @if($doc->description)
                                    <div class="small mt-1 text-muted">{{ $doc->description }}</div>
                                @endif
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">Open</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        @if($canUploadFollowupDocuments ?? false)
            <div class="card-body border-top bg-light bg-opacity-50">
                <h6 class="fw-semibold mb-2">Add more documents</h6>
                <p class="text-muted small mb-3">Upload additional files while your request is <strong>{{ $statusLabel($request->status) }}</strong>. The office will be notified.</p>
                <form method="POST" action="{{ route('citizen.requests.documents.store', $request) }}" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label small">Files <span class="text-danger">*</span></label>
                        <input type="file" name="attachments[]" class="form-control form-control-sm @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror" multiple required accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*">
                        <div class="form-text">PDF or images, up to 15 files, 12 MB each.</div>
                        @error('attachments')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('attachments.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Note <span class="text-muted">(optional)</span></label>
                        <input type="text" name="note" class="form-control form-control-sm @error('note') is-invalid @enderror" maxlength="500" value="{{ old('note') }}" placeholder="e.g. Replacement scan for missing ID">
                        @error('note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-upload me-1"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endsection
