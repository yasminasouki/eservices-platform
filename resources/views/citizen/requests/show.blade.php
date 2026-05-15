@extends('layouts.citizen')

@section('title', 'Request #'.$request->id)

@php
    use Illuminate\Support\Facades\Storage;
    $statusConfig = fn (string $s) => match ($s) {
        'pending'           => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'clock',        'label' => 'Pending'],
        'in_review'         => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'eye',          'label' => 'In Review'],
        'missing_documents' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'icon' => 'paperclip',   'label' => 'Missing Documents'],
        'approved'          => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'check-circle', 'label' => 'Approved'],
        'rejected'          => ['bg' => '#fde8e8', 'color' => '#991b1b', 'icon' => 'x-circle',    'label' => 'Rejected'],
        'completed'         => ['bg' => '#ede9fe', 'color' => '#4c1d95', 'icon' => 'patch-check', 'label' => 'Completed'],
        default             => ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => 'circle',       'label' => ucfirst($s)],
    };
    $cfg = $statusConfig($request->status);
@endphp

@push('styles')
<style>
    /* ── Breadcrumb ── */
    .req-breadcrumb { display: flex; align-items: center; gap: .4rem; font-size: .8rem; color: #b4a0d4; margin-bottom: 1.25rem; }
    .req-breadcrumb a { color: #8b5cf6; text-decoration: none; font-weight: 500; }
    .req-breadcrumb a:hover { text-decoration: underline; }
    .req-breadcrumb .sep { color: #ddd6fe; }

    /* ── Page header ── */
    .req-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .req-title { font-size: 1.3rem; font-weight: 800; color: #1f1235; margin-bottom: .15rem; }
    .req-sub   { font-size: .82rem; color: #b4a0d4; margin: 0; }
    .req-status-pill {
        display: inline-flex; align-items: center; gap: .45rem;
        font-size: .82rem; font-weight: 700;
        padding: .45rem 1rem; border-radius: 999px;
        white-space: nowrap; flex-shrink: 0;
    }

    /* ── Base card ── */
    .detail-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(124,58,237,.06);
        border: 1px solid #f0ecff;
        overflow: hidden;
        margin-bottom: 1.25rem;
    }
    .detail-card:last-child { margin-bottom: 0; }
    .detail-card-header {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;
        padding: .9rem 1.25rem .8rem;
        border-bottom: 1px solid #f3f0ff;
    }
    .detail-card-title {
        font-weight: 700; font-size: .9rem; color: #1f1235;
        display: flex; align-items: center; gap: .5rem;
    }
    .detail-card-title i { color: #a78bfa; }
    .detail-card-body { padding: 1.1rem 1.25rem; }

    /* ── Info rows ── */
    .info-row { display: flex; gap: .5rem; margin-bottom: .7rem; font-size: .86rem; }
    .info-row:last-child { margin-bottom: 0; }
    .info-label { width: 110px; flex-shrink: 0; color: #b4a0d4; font-weight: 600; font-size: .78rem; padding-top: .1rem; }
    .info-value { color: #1f1235; font-weight: 500; flex: 1; }

    /* ── Alert boxes ── */
    .req-alert {
        display: flex; align-items: flex-start; gap: .75rem;
        border-radius: 11px; padding: .9rem 1rem; margin-bottom: 1.25rem;
        font-size: .86rem;
    }
    .req-alert i { font-size: 1.1rem; flex-shrink: 0; margin-top: .05rem; }
    .req-alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .req-alert-warning i { color: #d97706; }
    .req-alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .req-alert-danger  i { color: #ef4444; }
    .req-alert-info    { background: #f0f7ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
    .req-alert-info    i { color: #3b82f6; }

    /* ── Payment alert ── */
    .pay-alert {
        background: #fffbeb; border: 1px solid #fde68a;
        border-radius: 13px; padding: 1rem 1.25rem;
        display: flex; align-items: center; justify-content: space-between;
        gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;
    }
    .pay-alert-text strong { color: #92400e; }
    .pay-alert-text span   { font-size: .85rem; color: #78350f; }
    .btn-pay-now {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #fde68a; border: 1px solid #fbbf24;
        color: #78350f; font-weight: 700; font-size: .85rem;
        padding: .5rem 1.1rem; border-radius: 9px;
        text-decoration: none; transition: background .15s; white-space: nowrap;
    }
    .btn-pay-now:hover { background: #fbbf24; color: #78350f; }

    /* ── Chat button ── */
    .btn-chat {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #ede9fe; border: 1px solid #ddd6fe;
        color: #4c1d95; font-weight: 700; font-size: .82rem;
        padding: .4rem .9rem; border-radius: 9px;
        text-decoration: none; transition: background .15s;
    }
    .btn-chat:hover { background: #ddd6fe; color: #3b0764; }

    /* ── Documents ── */
    .doc-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; flex-wrap: wrap;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #faf9ff;
        transition: background .12s;
    }
    .doc-row:last-child { border-bottom: none; }
    .doc-row:hover { background: #faf8ff; }
    .doc-icon {
        width: 36px; height: 36px; border-radius: 9px;
        background: #ede9fe; color: #8b5cf6;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .doc-name { font-weight: 600; font-size: .86rem; color: #1f1235; }
    .doc-meta { font-size: .75rem; color: #b4a0d4; margin-top: .1rem; }
    .doc-actions { display: flex; gap: .4rem; flex-shrink: 0; }
    .btn-doc {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .75rem; font-weight: 600; padding: .3rem .7rem;
        border-radius: 7px; text-decoration: none; transition: background .12s;
        border: 1px solid transparent;
    }
    .btn-doc-open { background: #f3f4f6; color: #374151; border-color: #e5e7eb; }
    .btn-doc-open:hover { background: #e5e7eb; }
    .btn-doc-dl   { background: #ede9fe; color: #4c1d95; border-color: #ddd6fe; }
    .btn-doc-dl:hover { background: #ddd6fe; }

    /* ── Upload section ── */
    .upload-section {
        background: #faf8ff; border-top: 1px solid #f0ecff; padding: 1.1rem 1.25rem;
    }
    .upload-title { font-weight: 700; font-size: .88rem; color: #1f1235; margin-bottom: .25rem; }
    .upload-sub   { font-size: .8rem; color: #b4a0d4; margin-bottom: .85rem; }

    /* ── Stars ── */
    .star-row { display: flex; gap: .15rem; margin-bottom: .5rem; }
    .star-row i { color: #fbbf24; font-size: 1.05rem; }
    .star-row i.bi-star { color: #e5e7eb; }

    /* ── Office reply ── */
    .office-reply {
        background: #f0fdf4; border-left: 3px solid #6ee7b7;
        border-radius: 0 8px 8px 0; padding: .75rem 1rem; margin-top: .75rem;
    }
    .office-reply-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #059669; margin-bottom: .3rem; }
    .office-reply-text  { font-size: .85rem; color: #374151; }
    .office-reply-date  { font-size: .74rem; color: #b4a0d4; margin-top: .3rem; }

    /* ── QR card ── */
    .qr-card-body { padding: 1.1rem 1.25rem; text-align: center; }

    /* ── Status card ── */
    .status-card-body {
        padding: 1rem 1.25rem;
        display: flex; flex-direction: column; gap: .6rem;
    }
    .status-pill-lg {
        display: inline-flex; align-items: center; gap: .5rem;
        font-weight: 700; font-size: .88rem;
        padding: .55rem 1.1rem; border-radius: 999px;
        align-self: flex-start;
    }

    /* ── Payment badge ── */
    .pay-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #fef3c7; color: #92400e;
        font-size: .78rem; font-weight: 700;
        padding: .3rem .75rem; border-radius: 999px;
    }
    .paid-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #d1fae5; color: #065f46;
        font-size: .78rem; font-weight: 700;
        padding: .3rem .75rem; border-radius: 999px;
    }

    /* ── Btn rate ── */
    .btn-rate {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #d1fae5; border: 1px solid #a7f3d0;
        color: #065f46; font-weight: 700; font-size: .82rem;
        padding: .45rem .9rem; border-radius: 9px;
        text-decoration: none; transition: background .15s;
    }
    .btn-rate:hover { background: #a7f3d0; color: #064e3b; }

    /* ── Empty docs ── */
    .no-docs { padding: 1.5rem 1.25rem; text-align: center; color: #c4b5fd; font-size: .84rem; }
    .no-docs i { font-size: 1.6rem; display: block; margin-bottom: .4rem; }
</style>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    <div class="req-breadcrumb">
        <a href="{{ route('citizen.requests.index') }}"><i class="bi bi-folder2-open me-1"></i>My requests</a>
        <span class="sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
        <span>Request #{{ $request->id }}</span>
    </div>

    {{-- Payment warning --}}
    @if(!empty($awaitingPayment))
        <div class="pay-alert">
            <div class="pay-alert-text">
                <strong><i class="bi bi-cash-coin me-1"></i>Payment required</strong><br>
                <span>This service has a fee of <strong>${{ number_format((float)($request->service?->price ?? 0), 2) }} USD</strong>. The office will not process your request until payment is completed.</span>
            </div>
            <a href="{{ route('citizen.requests.pay', $request) }}" class="btn-pay-now">
                <i class="bi bi-credit-card"></i> Pay now
            </a>
        </div>
    @endif

    {{-- Page header --}}
    <div class="req-header">
        <div>
            <div class="req-title">Request #{{ $request->id }}</div>
            <p class="req-sub">Save or print the QR code below to check status anytime without logging in.</p>
        </div>
        <span class="req-status-pill" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
            <i class="bi bi-{{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
        </span>
    </div>

    <div class="row g-4">

        {{-- ═══ LEFT COLUMN ═══ --}}
        <div class="col-lg-8">

            {{-- Service & office --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="detail-card-title"><i class="bi bi-building"></i> Service & Office</div>
                </div>
                <div class="detail-card-body">
                    <div class="info-row">
                        <div class="info-label">Service</div>
                        <div class="info-value">{{ $request->service?->name ?? '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Office</div>
                        <div class="info-value">{{ $request->governmentOffice?->name ?? '—' }}</div>
                    </div>
                    @if($request->governmentOffice?->address)
                        <div class="info-row">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $request->governmentOffice->address }}</div>
                        </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label">Submitted</div>
                        <div class="info-value">
                            {{ ($request->submitted_at ?? $request->created_at)?->format('M j, Y — H:i') ?? '—' }}
                        </div>
                    </div>
                    @if($request->notes)
                        <div class="info-row">
                            <div class="info-label">Your notes</div>
                            <div class="info-value">{!! nl2br(e($request->notes)) !!}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Rejection / missing docs alerts --}}
            @if($request->status === 'rejected' && $request->rejection_reason)
                <div class="req-alert req-alert-danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <div><strong>Rejected:</strong> {{ $request->rejection_reason }}</div>
                </div>
            @endif
            @if($request->status === 'missing_documents' && $request->missing_docs_note)
                <div class="req-alert req-alert-warning">
                    <i class="bi bi-paperclip"></i>
                    <div><strong>Missing documents:</strong> {{ $request->missing_docs_note }}</div>
                </div>
            @endif

            {{-- Payment info --}}
            @if($request->payment)
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title"><i class="bi bi-wallet2"></i> Payment</div>
                        @if($request->payment->status === 'completed')
                            <span class="paid-badge"><i class="bi bi-check-circle-fill"></i> Paid</span>
                        @else
                            <span class="pay-badge"><i class="bi bi-clock"></i> Awaiting payment</span>
                        @endif
                    </div>
                    <div class="detail-card-body">
                        <div class="info-row">
                            <div class="info-label">Amount</div>
                            <div class="info-value">${{ number_format((float)$request->payment->amount, 2) }} USD</div>
                        </div>
                        @if($request->payment->status === 'completed' && $request->payment->paid_at)
                            <div class="info-row">
                                <div class="info-label">Paid on</div>
                                <div class="info-value">{{ $request->payment->paid_at->format('M j, Y H:i') }}</div>
                            </div>
                        @endif
                        @if($request->payment->method && $request->payment->method !== 'pending')
                            <div class="info-row">
                                <div class="info-label">Method</div>
                                <div class="info-value">{{ ucfirst($request->payment->method) }}</div>
                            </div>
                        @endif
                        @if($request->payment->transaction_id)
                            <div class="info-row">
                                <div class="info-label">Transaction</div>
                                <div class="info-value" style="font-family:monospace;font-size:.8rem;word-break:break-all;">
                                    {{ $request->payment->transaction_id }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Documents --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="detail-card-title"><i class="bi bi-paperclip"></i> Documents</div>
                    <span class="total-badge" style="background:#ede9fe;color:#6d28d9;font-size:.72rem;font-weight:700;padding:.15rem .55rem;border-radius:999px;">
                        {{ $request->documents->count() }}
                    </span>
                </div>

                @if($request->documents->isEmpty())
                    <div class="no-docs">
                        <i class="bi bi-file-earmark"></i>
                        No documents on file yet.
                    </div>
                @else
                    @foreach($request->documents as $doc)
                        <div class="doc-row">
                            <div class="d-flex align-items-center gap-3">
                                <div class="doc-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <div>
                                    <div class="doc-name">{{ $doc->file_name }}</div>
                                    <div class="doc-meta">
                                        {{ str($doc->type)->replace('_', ' ')->title() }}
                                        · {{ $doc->uploaded_by === 'office' ? 'Uploaded by office' : 'Uploaded by you' }}
                                        @if($doc->description) · {{ $doc->description }} @endif
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn-doc btn-doc-open">
                                    <i class="bi bi-box-arrow-up-right"></i> Open
                                </a>
                                <a href="{{ route('citizen.requests.documents.download', [$request, $doc]) }}" class="btn-doc btn-doc-dl">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($canUploadFollowupDocuments ?? false)
                    <div class="upload-section">
                        <div class="upload-title"><i class="bi bi-upload me-1" style="color:#a78bfa;"></i>Add more documents</div>
                        <div class="upload-sub">Upload additional files while your request is <strong>{{ $cfg['label'] }}</strong>. The office will be notified.</div>
                        <form method="POST" action="{{ route('citizen.requests.documents.store', $request) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#6d5b8e;">Files <span class="text-danger">*</span></label>
                                <input type="file" name="attachments[]" multiple required
                                       accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                                       class="form-control form-control-sm @error('attachments') is-invalid @enderror">
                                <div class="form-text" style="font-size:.75rem;">PDF or images — up to 15 files, 12 MB each.</div>
                                @error('attachments') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#6d5b8e;">Note <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" name="note" maxlength="500" value="{{ old('note') }}"
                                       placeholder="e.g. Replacement scan for missing ID"
                                       class="form-control form-control-sm @error('note') is-invalid @enderror">
                                @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <button type="submit" class="btn-chat">
                                <i class="bi bi-upload"></i> Upload documents
                            </button>
                        </form>
                    </div>
                @endif
            </div>

        </div>

        {{-- ═══ RIGHT COLUMN ═══ --}}
        <div class="col-lg-4">

            {{-- Status card --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="detail-card-title"><i class="bi bi-info-circle"></i> Status</div>
                </div>
                <div class="status-card-body">
                    <span class="status-pill-lg" style="background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
                        <i class="bi bi-{{ $cfg['icon'] }}"></i> {{ $cfg['label'] }}
                    </span>
                    <p style="font-size:.82rem;color:#9d7ecf;margin:0;">
                        @switch($request->status)
                            @case('pending') Your request has been submitted and is waiting to be reviewed. @break
                            @case('in_review') An office agent is currently reviewing your request. @break
                            @case('missing_documents') The office requires additional documents from you. @break
                            @case('approved') Your request has been approved. @break
                            @case('rejected') Your request was not approved. See the reason below. @break
                            @case('completed') Your request has been fully processed. @break
                            @default Processing… @break
                        @endswitch
                    </p>
                </div>
            </div>

            {{-- Live chat --}}
            @if($request->governmentOffice && empty($awaitingPayment))
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title"><i class="bi bi-chat-dots"></i> Live chat</div>
                    </div>
                    <div class="detail-card-body">
                        <p style="font-size:.82rem;color:#9d7ecf;margin-bottom:.85rem;">
                            Message {{ $request->governmentOffice->name }} directly — not limited to this request.
                        </p>
                        <a href="{{ route('citizen.offices.chat', $request->governmentOffice) }}" class="btn-chat w-100 justify-content-center">
                            <i class="bi bi-chat-dots"></i> Open live chat
                        </a>
                    </div>
                </div>
            @endif

            {{-- QR tracking --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="detail-card-title"><i class="bi bi-qr-code"></i> QR tracking</div>
                </div>
                <div class="qr-card-body">
                    @include('partials.service-request-public-qr', [
                        'trackingUrl'        => $trackingUrl,
                        'trackingQrDataUri'  => $trackingQrDataUri,
                        'referenceCode'      => $request->qr_code,
                    ])
                </div>
            </div>

            {{-- Feedback --}}
            @if($request->status === 'completed')
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title"><i class="bi bi-star"></i> Your feedback</div>
                    </div>
                    <div class="detail-card-body">
                        @if($request->feedback)
                            <div class="star-row">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $request->feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </div>
                            @if($request->feedback->comment)
                                <p style="font-size:.85rem;color:#374151;margin-bottom:.75rem;">{{ $request->feedback->comment }}</p>
                            @endif
                            @if($request->feedback->office_reply)
                                <div class="office-reply">
                                    <div class="office-reply-label">Office reply</div>
                                    <div class="office-reply-text">{!! nl2br(e($request->feedback->office_reply)) !!}</div>
                                    @if($request->feedback->replied_at)
                                        <div class="office-reply-date">{{ $request->feedback->replied_at->format('M j, Y H:i') }}</div>
                                    @endif
                                </div>
                            @else
                                <p style="font-size:.8rem;color:#b4a0d4;margin:0;">The office has not replied yet.</p>
                            @endif
                        @else
                            <p style="font-size:.84rem;color:#9d7ecf;margin-bottom:.85rem;">
                                Tell us how this service went — it helps municipalities improve.
                            </p>
                            <a href="{{ route('citizen.feedback.request.create', $request) }}" class="btn-rate w-100 justify-content-center">
                                <i class="bi bi-star"></i> Rate this request
                            </a>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection
