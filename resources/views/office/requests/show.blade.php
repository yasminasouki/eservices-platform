@extends('layouts.office')

@section('title', 'Request #'.$request->id)

@php
    use Illuminate\Support\Facades\Storage;
    $statusConfig = fn (string $status) => match ($status) {
        'pending'           => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'clock'],
        'in_review'         => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'eye'],
        'missing_documents' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'icon' => 'paperclip'],
        'approved'          => ['bg' => '#dcfce7', 'color' => '#14532d', 'icon' => 'check-circle'],
        'rejected'          => ['bg' => '#fde8e8', 'color' => '#991b1b', 'icon' => 'x-circle'],
        'completed'         => ['bg' => '#ede9fe', 'color' => '#4c1d95', 'icon' => 'patch-check'],
        default             => ['bg' => '#f3f4f6', 'color' => '#374151', 'icon' => 'circle'],
    };
    $statusLabel = fn (string $status) => str($status)->replace('_', ' ')->title()->toString();
    $reqCfg = $statusConfig($request->status);
@endphp

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }


    /* Status pill inline */
    .status-pill {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .8rem; font-weight: 700;
        padding: .38rem .9rem; border-radius: 999px; white-space: nowrap;
    }
    .status-pill i { font-size: .76rem; }

    /* Cards */
    .green-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 12px rgba(21,128,61,.06);
        overflow: hidden; margin-bottom: 1.25rem;
    }
    .green-card-header {
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem;
        padding: .9rem 1.25rem; border-bottom: 1px solid #f0fdf4;
        font-weight: 700; font-size: .9rem; color: #0f2d13;
        background: #f0fdf4;
    }
    .green-card-body { padding: 1.1rem 1.25rem; }
    .green-card-body-p0 { padding: 0; }

    /* DL grid */
    .info-grid { display: grid; grid-template-columns: 7rem 1fr; gap: .45rem 1rem; }
    .info-label { font-size: .78rem; font-weight: 600; color: #52916b; padding-top: .1rem; }
    .info-value { font-size: .875rem; color: #1a2e1c; }

    /* Chat button */
    .btn-chat {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #dcfce7; border: 1px solid #86efac; color: #14532d;
        font-weight: 700; font-size: .8rem; padding: .35rem .85rem;
        border-radius: 8px; text-decoration: none; transition: background .15s;
    }
    .btn-chat:hover { background: #bbf7d0; color: #0f2d13; }

    /* Document list */
    .doc-item {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
        padding: .85rem 1.25rem; border-bottom: 1px solid #f0fdf4;
    }
    .doc-item:last-child { border-bottom: none; }
    .doc-name { font-weight: 700; font-size: .875rem; color: #0f2d13; margin-bottom: .15rem; }
    .doc-meta { font-size: .76rem; color: #52916b; }
    .doc-desc { font-size: .82rem; color: #374151; margin-top: .25rem; }
    .btn-doc-dl {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .76rem; font-weight: 600; padding: .3rem .75rem;
        border-radius: 7px; border: 1px solid #86efac;
        background: #f0fdf4; color: #15803d; text-decoration: none; white-space: nowrap;
        transition: background .12s;
    }
    .btn-doc-dl:hover { background: #dcfce7; color: #0f2d13; }

    /* Upload form */
    .green-card-body .form-label { font-size: .8rem; font-weight: 600; color: #14532d; }
    .green-card-body .form-control,
    .green-card-body .form-select { font-size: .85rem; border-color: #d1fae5; }
    .green-card-body .form-control:focus,
    .green-card-body .form-select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
    .btn-upload {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .84rem; padding: .45rem 1.1rem;
        border-radius: 9px; transition: background .15s; cursor: pointer;
    }
    .btn-upload:hover { background: #15803d; }

    /* Status form */
    .btn-save-status {
        display: block; width: 100%;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .88rem; padding: .55rem 1rem;
        border-radius: 9px; transition: background .15s; cursor: pointer; text-align: center;
    }
    .btn-save-status:hover { background: #15803d; }

    /* Status history timeline */
    .timeline-item { padding: .75rem 0; border-bottom: 1px solid #f0fdf4; }
    .timeline-item:last-child { border-bottom: none; }
    .timeline-meta { display: flex; justify-content: space-between; gap: .5rem; flex-wrap: wrap;
        font-size: .76rem; color: #52916b; margin-bottom: .35rem; }
    .timeline-pills { display: flex; align-items: center; gap: .3rem; flex-wrap: wrap; }
    .timeline-note { font-size: .79rem; color: #374151; margin-top: .4rem; }

    .no-docs { font-size: .84rem; color: #52916b; padding: 1.1rem 1.25rem; margin: 0; }
    .no-history { font-size: .84rem; color: #52916b; }

    /* Crypto payment card */
    .crypto-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .72rem; font-weight: 700; padding: .25rem .65rem;
        border-radius: 999px; white-space: nowrap;
    }
    .crypto-verify-result {
        border-radius: 10px; padding: .75rem 1rem;
        font-size: .8rem; margin-top: .75rem; display: none;
    }
    .crypto-verify-result.show { display: block; }
    .crypto-verify-result.ok  { background: #f0fdf4; border: 1px solid #86efac; color: #14532d; }
    .crypto-verify-result.warn { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
    .crypto-verify-result.err { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
    .crypto-verify-row { display: flex; justify-content: space-between; gap: .5rem; margin-bottom: .3rem; flex-wrap: wrap; }
    .crypto-verify-label { font-weight: 700; flex-shrink: 0; }
    .crypto-verify-val { font-family: monospace; font-size: .78rem; word-break: break-all; }
    .btn-crypto-verify {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #eff6ff; border: 1px solid #93c5fd; color: #1e40af;
        font-size: .8rem; font-weight: 700; padding: .35rem .85rem;
        border-radius: 8px; cursor: pointer; transition: background .15s;
    }
    .btn-crypto-verify:hover { background: #dbeafe; }
    .btn-crypto-verify:disabled { opacity: .6; cursor: not-allowed; }
    .btn-crypto-approve {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #16a34a; border: none; color: #fff;
        font-size: .84rem; font-weight: 700; padding: .45rem 1.1rem;
        border-radius: 9px; cursor: pointer; transition: background .15s;
    }
    .btn-crypto-approve:hover { background: #15803d; }
</style>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <span>
            <a href="{{ route('office.requests.index', $office) }}" class="breadcrumb-link">{{ __('ui.office_req_title') }}</a>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">#{{ $request->id }}</span>
        </span>
    </nav>

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="page-title">Request #{{ $request->id }}</div>
            <p class="page-sub">{{ $office->name }}</p>
        </div>
        <span class="status-pill" style="background:{{ $reqCfg['bg'] }};color:{{ $reqCfg['color'] }};">
            <i class="bi bi-{{ $reqCfg['icon'] }}"></i>
            {{ $statusLabel($request->status) }}
        </span>
    </div>

    @include('partials.service-request-public-qr', [
        'trackingUrl'      => $trackingUrl,
        'trackingQrDataUri'=> $trackingQrDataUri,
        'referenceCode'    => $request->qr_code,
    ])

    <div class="row g-4">
        {{-- Left column --}}
        <div class="col-lg-7">

            {{-- Live chat --}}
            @if($request->citizen)
                <div class="green-card">
                    <div class="green-card-header">
                        <span><i class="bi bi-chat-dots me-2 text-success"></i>{{ __('ui.citizen_chat_title') }}</span>
                        <a href="{{ route('office.chat.show', [$office, $request->citizen]) }}" class="btn-chat">
                            <i class="bi bi-chat-dots"></i>{{ __('ui.office_req_show_open_thread', ['name' => $request->citizen->name]) }}
                        </a>
                    </div>
                    <div class="green-card-body">
                        <p class="mb-0" style="font-size:.83rem;color:#52916b;">
                            {{ __('ui.office_req_show_general_chat') }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Citizen & service --}}
            <div class="green-card">
                <div class="green-card-header">
                    <span><i class="bi bi-person me-2 text-success"></i>{{ __('ui.office_req_show_citizen_service') }}</span>
                </div>
                <div class="green-card-body">
                    <div class="info-grid">
                        <span class="info-label">{{ __('ui.office_req_show_lbl_citizen') }}</span>
                        <span class="info-value">{{ $request->citizen?->name ?? '—' }}</span>
                        <span class="info-label">{{ __('ui.office_req_show_lbl_email') }}</span>
                        <span class="info-value">{{ $request->citizen?->email ?? '—' }}</span>
                        <span class="info-label">{{ __('ui.office_req_show_lbl_phone') }}</span>
                        <span class="info-value">{{ $request->citizen?->phone ?? '—' }}</span>
                        <span class="info-label">{{ __('ui.office_req_show_lbl_service') }}</span>
                        <span class="info-value">{{ $request->service?->name ?? '—' }}</span>
                        @if($request->service?->category)
                            <span class="info-label">{{ __('ui.office_req_show_lbl_category') }}</span>
                            <span class="info-value">{{ $request->service->category->name }}</span>
                        @endif
                        <span class="info-label">{{ __('ui.office_req_show_lbl_submitted') }}</span>
                        <span class="info-value">{{ $request->submitted_at?->format('Y-m-d H:i') ?? $request->created_at?->format('Y-m-d H:i') ?? '—' }}</span>
                        <span class="info-label">{{ __('ui.office_req_show_lbl_notes') }}</span>
                        <span class="info-value">{!! $request->notes ? nl2br(e($request->notes)) : '—' !!}</span>
                    </div>
                </div>
            </div>

            {{-- Documents --}}
            <div class="green-card">
                <div class="green-card-header">
                    <span><i class="bi bi-paperclip me-2 text-success"></i>{{ __('ui.office_req_show_documents') }}</span>
                </div>
                @if($request->documents->isEmpty())
                    <p class="no-docs">{{ __('ui.office_req_show_no_docs') }}</p>
                @else
                    <div class="green-card-body-p0">
                        @foreach($request->documents as $doc)
                            <div class="doc-item">
                                <div>
                                    <div class="doc-name">{{ $doc->file_name }}</div>
                                    <div class="doc-meta">
                                        {{ str($doc->type)->replace('_', ' ')->title() }}
                                        · {{ $doc->uploaded_by === 'office' ? 'Office' : 'Citizen' }}
                                        @if($doc->uploader)· {{ $doc->uploader->name }}@endif
                                    </div>
                                    @if($doc->description)
                                        <div class="doc-desc">{{ $doc->description }}</div>
                                    @endif
                                </div>
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn-doc-dl">
                                    <i class="bi bi-download"></i>Open
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Upload document --}}
            <div class="green-card">
                <div class="green-card-header">
                    <span><i class="bi bi-upload me-2 text-success"></i>{{ __('ui.office_req_show_upload_doc') }}</span>
                </div>
                <div class="green-card-body">
                    <form method="POST" action="{{ route('office.requests.documents.store', [$office, $request]) }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">{{ __('ui.office_req_show_col_file') }} <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-sm" required>
                            <div class="form-text" style="font-size:.75rem;color:#52916b;">Max 12 MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('ui.office_req_show_col_type') }} <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="certificate">{{ __('ui.office_req_show_doc_cert') }}</option>
                                <option value="generated">{{ __('ui.office_req_show_doc_gen') }}</option>
                                <option value="receipt">{{ __('ui.office_req_show_doc_receipt') }}</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('ui.office_req_show_col_desc') }}</label>
                            <input type="text" name="description" class="form-control form-control-sm" maxlength="500" value="{{ old('description') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-upload">
                                <i class="bi bi-upload"></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-lg-5">

            {{-- Update status --}}
            <div class="green-card">
                <div class="green-card-header">
                    <span><i class="bi bi-arrow-repeat me-2 text-success"></i>{{ __('ui.office_req_show_update_status') }}</span>
                </div>
                <div class="green-card-body">
                    <form method="POST" action="{{ route('office.requests.status', [$office, $request]) }}" id="office-status-form">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('ui.office_req_show_status_label') }} <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select form-select-sm" required>
                                @foreach(\App\Models\ServiceRequest::STATUSES as $s)
                                    <option value="{{ $s }}" @selected(old('status', $request->status) === $s)>
                                        {{ $statusLabel($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3" id="field-rejection" style="display: none;">
                            <label for="rejection_reason" class="form-label">{{ __('ui.office_req_show_rejection_reason') }} <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control form-control-sm" rows="3" maxlength="5000">{{ old('rejection_reason', $request->rejection_reason) }}</textarea>
                        </div>
                        <div class="mb-3" id="field-missing" style="display: none;">
                            <label for="missing_docs_note" class="form-label">{{ __('ui.office_req_show_missing_docs_note') }} <span class="text-danger">*</span></label>
                            <textarea name="missing_docs_note" id="missing_docs_note" class="form-control form-control-sm" rows="3" maxlength="5000">{{ old('missing_docs_note', $request->missing_docs_note) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status_note" class="form-label">{{ __('ui.office_req_show_status_log_note') }} <span class="text-muted fw-normal">({{ __('ui.citizen_feedback_optional') }})</span></label>
                            <input type="text" name="status_note" id="status_note" class="form-control form-control-sm" maxlength="2000" value="{{ old('status_note') }}">
                            <div class="form-text" style="font-size:.74rem;color:#52916b;">Appended to the audit trail when you save.</div>
                        </div>
                        <button type="submit" class="btn-save-status">
                            <i class="bi bi-check2-circle me-1"></i>{{ __('ui.office_req_show_save_status') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Card payment (office review) --}}
            @if($request->payment && $request->payment->method === 'card')
                @php
                    $pay = $request->payment;
                    $gw  = $pay->gateway_response ?? [];
                    $txId = $pay->transaction_id ?? $gw['stripe_elements_intent_id'] ?? null;
                @endphp
                <div class="green-card">
                    <div class="green-card-header">
                        <span><i class="bi bi-credit-card me-2 text-success"></i>{{ __('ui.office_req_show_stripe') }}</span>
                        @if($pay->status === 'completed')
                            <span class="crypto-badge" style="background:#dcfce7;color:#14532d;">
                                <i class="bi bi-check-circle-fill"></i>Approved
                            </span>
                        @else
                            <span class="crypto-badge" style="background:#fef3c7;color:#92400e;">
                                <i class="bi bi-hourglass-split"></i>Pending
                            </span>
                        @endif
                    </div>
                    <div class="green-card-body">
                        <div class="info-grid">
                            <span class="info-label">{{ __('ui.office_req_show_lbl_amount') }}</span>
                            <span class="info-value fw-bold">${{ number_format((float) $pay->amount, 2) }} USD</span>
                            <span class="info-label">{{ __('ui.office_req_show_lbl_transaction') }}</span>
                            <span class="info-value" style="font-family:monospace;font-size:.8rem;word-break:break-all;">
                                @if($txId)
                                    {{ $txId }}
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </span>
                            @if($pay->paid_at)
                                <span class="info-label">{{ __('ui.office_req_show_lbl_paid_at') }}</span>
                                <span class="info-value">{{ $pay->paid_at->format('M j, Y g:i A') }}</span>
                            @endif
                        </div>
                        @if($pay->status === 'completed')
                            <p style="font-size:.82rem;color:#15803d;margin:.75rem 0 0;">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ __('ui.office_req_show_stripe_confirmed') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Crypto payment (office review) --}}
            @if($request->payment && $request->payment->method === 'cryptocurrency')
                @php
                    $pay = $request->payment;
                    $gw = $pay->gateway_response ?? [];
                    $cryptoAsset = strtoupper($gw['crypto_quote']['asset'] ?? '—');
                    $cryptoAmount = $gw['crypto_quote']['crypto_amount'] ?? null;
                    $cryptoWallet = $pay->crypto_wallet_address ?? $gw['crypto_wallet'] ?? null;
                    $cryptoTxRef = $gw['crypto_tx_reference'] ?? null;
                    $cryptoQuotedAt = $gw['crypto_quoted_at'] ?? null;
                    $cryptoSubmittedAt = $gw['crypto_citizen_submitted_at'] ?? null;
                    $cryptoApprovedAt = $gw['crypto_approved_at'] ?? null;
                    $isApproved = $pay->status === 'completed';
                    $checkUrl = route('office.requests.crypto.check', [$office, $request]);
                    $approveUrl = route('office.requests.crypto.approve', [$office, $request]);
                @endphp
                <div class="green-card" id="crypto-payment-card">
                    <div class="green-card-header">
                        <span><i class="bi bi-currency-bitcoin me-2 text-success"></i>{{ __('ui.office_req_show_crypto') }}</span>
                        @if($isApproved)
                            <span class="crypto-badge" style="background:#dcfce7;color:#14532d;">
                                <i class="bi bi-check-circle-fill"></i>Approved
                            </span>
                        @elseif($cryptoTxRef)
                            <span class="crypto-badge" style="background:#fef3c7;color:#92400e;">
                                <i class="bi bi-hourglass-split"></i>Awaiting approval
                            </span>
                        @else
                            <span class="crypto-badge" style="background:#f3f4f6;color:#374151;">
                                <i class="bi bi-clock"></i>Pending tx
                            </span>
                        @endif
                    </div>
                    <div class="green-card-body">
                        <div class="info-grid mb-3">
                            <span class="info-label">{{ __('ui.office_req_show_lbl_asset') }}</span>
                            <span class="info-value">{{ $cryptoAsset }}</span>
                            <span class="info-label">{{ __('ui.office_req_show_lbl_amount') }}</span>
                            <span class="info-value font-monospace">
                                {{ $cryptoAmount ?? '—' }}
                                @if($cryptoAmount) <span style="font-size:.75rem;color:#52916b;">≈ ${{ number_format((float)$pay->amount, 2) }} USD</span>@endif
                            </span>
                            <span class="info-label">{{ __('ui.office_req_show_lbl_wallet') }}</span>
                            <span class="info-value" style="font-family:monospace;font-size:.8rem;word-break:break-all;">{{ $cryptoWallet ?? '—' }}</span>
                            <span class="info-label">{{ __('ui.office_req_show_lbl_tx_ref') }}</span>
                            <span class="info-value" style="font-family:monospace;font-size:.8rem;word-break:break-all;">
                                @if($cryptoTxRef)
                                    {{ $cryptoTxRef }}
                                @else
                                    <span style="color:#9ca3af;">{{ __('ui.office_req_show_not_submitted') }}</span>
                                @endif
                            </span>
                            @if($cryptoQuotedAt)
                                <span class="info-label">{{ __('ui.office_req_show_lbl_quoted_at') }}</span>
                                <span class="info-value">{{ \Illuminate\Support\Carbon::parse($cryptoQuotedAt)->format('M j, Y g:i A') }}</span>
                            @endif
                            @if($cryptoSubmittedAt)
                                <span class="info-label">{{ __('ui.office_req_show_lbl_citizen_sent') }}</span>
                                <span class="info-value">{{ \Illuminate\Support\Carbon::parse($cryptoSubmittedAt)->format('M j, Y g:i A') }}</span>
                            @endif
                            @if($isApproved && $cryptoApprovedAt)
                                <span class="info-label">{{ __('ui.office_req_show_lbl_approved_at') }}</span>
                                <span class="info-value">{{ \Illuminate\Support\Carbon::parse($cryptoApprovedAt)->format('M j, Y g:i A') }}</span>
                            @endif
                        </div>

                        {{-- Blockchain verify --}}
                        @if($cryptoTxRef && !$isApproved)
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <button type="button" class="btn-crypto-verify" id="crypto-verify-btn"
                                        data-check-url="{{ $checkUrl }}"
                                        data-tx-hash="{{ $cryptoTxRef }}">
                                    <i class="bi bi-search"></i>{{ __('ui.office_req_show_verify_blockchain') }}
                                </button>
                            </div>
                            <div class="crypto-verify-result" id="crypto-verify-result"></div>
                        @elseif(!$cryptoTxRef && !$isApproved)
                            <p style="font-size:.8rem;color:#9ca3af;margin:0 0 .75rem;">
                                <i class="bi bi-info-circle me-1"></i>{{ __('ui.office_req_show_waiting_hash') }}
                            </p>
                        @endif

                        {{-- Approve button --}}
                        @if(!$isApproved)
                            <form method="POST" action="{{ $approveUrl }}" id="crypto-approve-form" class="mt-2">
                                @csrf
                                <button type="submit" class="btn-crypto-approve"
                                        onclick="return confirm('{{ __('ui.office_req_show_approve_activate') }}?')">
                                    <i class="bi bi-check2-circle me-1"></i>{{ __('ui.office_req_show_approve_activate') }}
                                </button>
                            </form>
                        @else
                            <p style="font-size:.82rem;color:#15803d;margin:.5rem 0 0;">
                                <i class="bi bi-check-circle-fill me-1"></i>{{ __('ui.office_req_show_crypto_verified') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Status history --}}
            <div class="green-card">
                <div class="green-card-header">
                    <span><i class="bi bi-clock-history me-2 text-success"></i>{{ __('ui.office_req_show_history') }}</span>
                </div>
                <div class="green-card-body">
                    @if($request->statusLogs->isEmpty())
                        <p class="no-history mb-0">{{ __('ui.office_req_show_no_history') }}</p>
                    @else
                        @foreach($request->statusLogs as $log)
                            <div class="timeline-item">
                                <div class="timeline-meta">
                                    <span>{{ $log->created_at?->format('M j, Y g:i a') }}</span>
                                    @if($log->changedBy)
                                        <span>{{ $log->changedBy->name }}</span>
                                    @endif
                                </div>
                                <div class="timeline-pills">
                                    @if($log->from_status)
                                        @php $from = $statusConfig($log->from_status); @endphp
                                        <span class="status-pill" style="background:{{ $from['bg'] }};color:{{ $from['color'] }};font-size:.7rem;padding:.22rem .65rem;">
                                            <i class="bi bi-{{ $from['icon'] }}"></i>{{ $statusLabel($log->from_status) }}
                                        </span>
                                        <i class="bi bi-arrow-right" style="font-size:.7rem;color:#86efac;"></i>
                                    @endif
                                    @php $to = $statusConfig($log->to_status); @endphp
                                    <span class="status-pill" style="background:{{ $to['bg'] }};color:{{ $to['color'] }};font-size:.7rem;padding:.22rem .65rem;">
                                        <i class="bi bi-{{ $to['icon'] }}"></i>{{ $statusLabel($log->to_status) }}
                                    </span>
                                </div>
                                @if($log->notes)
                                    <div class="timeline-note">{{ $log->notes }}</div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('office-status-form');
            if (!form) return;
            var statusEl = document.getElementById('status');
            var rej = document.getElementById('field-rejection');
            var miss = document.getElementById('field-missing');
            function sync() {
                var v = statusEl.value;
                rej.style.display = (v === 'rejected') ? '' : 'none';
                miss.style.display = (v === 'missing_documents') ? '' : 'none';
            }
            statusEl.addEventListener('change', sync);
            sync();
        })();

        (function () {
            var btn = document.getElementById('crypto-verify-btn');
            var result = document.getElementById('crypto-verify-result');
            if (!btn || !result) return;

            btn.addEventListener('click', function () {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Checking…';
                result.className = 'crypto-verify-result';
                result.innerHTML = '';

                var url = btn.dataset.checkUrl + '?tx_hash=' + encodeURIComponent(btn.dataset.txHash);
                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.error && !data.found) {
                        result.className = 'crypto-verify-result err show';
                        result.innerHTML = '<strong><i class="bi bi-x-circle me-1"></i>Error:</strong> ' + esc(data.error);
                        return;
                    }

                    var cls = data.found && data.confirmed ? 'ok' : (data.found ? 'warn' : 'err');
                    var icon = data.found && data.confirmed ? 'check-circle-fill' : (data.found ? 'exclamation-triangle-fill' : 'x-circle-fill');
                    var headline = data.found && data.confirmed
                        ? 'Confirmed on-chain (' + data.confirmations + '/' + data.required_confirmations + ' confirmations)'
                        : (data.found
                            ? 'Found but not yet confirmed (' + (data.confirmations || 0) + '/' + (data.required_confirmations || '?') + ' confirmations)'
                            : 'Transaction not found');

                    var rows = '<div class="crypto-verify-row"><span class="crypto-verify-label"><i class="bi bi-' + icon + ' me-1"></i>' + esc(headline) + '</span></div>';
                    if (data.to_address) {
                        rows += row('To address', data.to_address);
                    }
                    if (data.amount) {
                        rows += row('Amount on-chain', data.amount + ' ' + (data.asset_label || ''));
                    }
                    if (data.expected_crypto_amount) {
                        rows += row('Expected amount', data.expected_crypto_amount);
                    }
                    if (data.explorer_url) {
                        rows += '<div class="crypto-verify-row"><span class="crypto-verify-label">Explorer</span>'
                            + '<a href="' + escAttr(data.explorer_url) + '" target="_blank" rel="noopener" style="font-size:.78rem;">'
                            + 'View on explorer <i class="bi bi-box-arrow-up-right"></i></a></div>';
                    }
                    if (data.error) {
                        rows += '<div class="mt-2" style="font-size:.77rem;opacity:.8;"><i class="bi bi-info-circle me-1"></i>' + esc(data.error) + '</div>';
                    }

                    result.className = 'crypto-verify-result ' + cls + ' show';
                    result.innerHTML = rows;
                })
                .catch(function () {
                    result.className = 'crypto-verify-result err show';
                    result.innerHTML = '<i class="bi bi-wifi-off me-1"></i>Could not reach the verification service. Try again.';
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-search"></i>Verify on blockchain';
                });
            });

            function row(label, val) {
                return '<div class="crypto-verify-row"><span class="crypto-verify-label">' + esc(label) + '</span>'
                    + '<span class="crypto-verify-val">' + esc(String(val)) + '</span></div>';
            }
            function esc(s) {
                var d = document.createElement('div'); d.textContent = s; return d.innerHTML;
            }
            function escAttr(s) {
                return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            }
        })();
    </script>
@endpush
