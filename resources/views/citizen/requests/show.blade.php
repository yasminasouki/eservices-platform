@extends('layouts.citizen')

@section('title', __('ui.request_hash', ['id' => $request->id]))

@php
    use Illuminate\Support\Facades\Storage;
    $statusConfig = fn (string $s) => match ($s) {
        'pending'           => ['bg' => '#fef3c7', 'color' => '#92400e', 'icon' => 'clock',        'label' => __('ui.citizen_show_step_submitted')],
        'in_review'         => ['bg' => '#dbeafe', 'color' => '#1e40af', 'icon' => 'eye',          'label' => __('ui.citizen_show_step_review')],
        'missing_documents' => ['bg' => '#ffedd5', 'color' => '#c2410c', 'icon' => 'paperclip',   'label' => __('ui.citizen_show_step_missing')],
        'approved'          => ['bg' => '#d1fae5', 'color' => '#065f46', 'icon' => 'check-circle', 'label' => __('ui.citizen_show_step_approved')],
        'rejected'          => ['bg' => '#fde8e8', 'color' => '#991b1b', 'icon' => 'x-circle',    'label' => __('ui.citizen_show_step_rejected')],
        'completed'         => ['bg' => '#ede9fe', 'color' => '#4c1d95', 'icon' => 'patch-check', 'label' => __('ui.citizen_show_step_completed')],
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

    /* ── Progress stepper ── */
    .progress-stepper { padding: 1.1rem 1.25rem 1.25rem; }
    .stepper-track { position: relative; display: flex; flex-direction: column; gap: 0; }
    .stepper-item {
        display: flex; align-items: flex-start; gap: .85rem;
        position: relative; padding-bottom: 1.15rem;
    }
    .stepper-item:last-child { padding-bottom: 0; }
    .stepper-line-wrap {
        display: flex; flex-direction: column; align-items: center;
        flex-shrink: 0; width: 28px;
    }
    .stepper-dot {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .8rem; flex-shrink: 0;
        border: 2px solid transparent;
        transition: background .3s, border-color .3s;
    }
    .stepper-dot.done  { background: #4c1d95; border-color: #4c1d95; color: #fff; }
    .stepper-dot.active{ background: #8b5cf6; border-color: #8b5cf6; color: #fff; box-shadow: 0 0 0 4px rgba(139,92,246,.18); }
    .stepper-dot.idle  { background: #f3f0ff; border-color: #ddd6fe; color: #c4b5fd; }
    .stepper-dot.bad   { background: #fde8e8; border-color: #fca5a5; color: #991b1b; }
    .stepper-connector {
        width: 2px; flex: 1; min-height: 18px;
        background: #e8e3ff; margin: 2px 0;
        transition: background .3s;
    }
    .stepper-connector.lit { background: #8b5cf6; }
    .stepper-text { padding-top: .15rem; }
    .stepper-label { font-size: .84rem; font-weight: 700; color: #1f1235; line-height: 1.2; }
    .stepper-label.idle-label { color: #c4b5fd; font-weight: 500; }
    .stepper-sublabel { font-size: .74rem; color: #b4a0d4; margin-top: .18rem; }
    .stepper-sublabel.active-sub { color: #8b5cf6; font-weight: 600; }
    .live-badge {
        display: inline-flex; align-items: center; gap: .3rem;
        background: #ede9fe; color: #6d28d9;
        font-size: .68rem; font-weight: 700; letter-spacing: .04em;
        padding: .12rem .5rem; border-radius: 999px;
        margin-left: .4rem; vertical-align: middle;
    }
    .live-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #8b5cf6; animation: pulse-dot 1.4s ease-in-out infinite; }
    @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
    .status-flash { animation: flash-in .5s ease; }
    @keyframes flash-in { 0%{opacity:0;transform:translateY(4px)} 100%{opacity:1;transform:translateY(0)} }
</style>
@endpush

@section('content')

    {{-- Breadcrumb --}}
    <div class="req-breadcrumb">
        <a href="{{ route('citizen.requests.index') }}"><i class="bi bi-folder2-open me-1"></i>{{ __('ui.my_requests') }}</a>
        <span class="sep"><i class="bi bi-chevron-right" style="font-size:.65rem;"></i></span>
        <span>{{ __('ui.request_hash', ['id' => $request->id]) }}</span>
    </div>

    {{-- Payment warning --}}
    @if(!empty($awaitingPayment))
        <div class="pay-alert">
            <div class="pay-alert-text">
                <strong><i class="bi bi-cash-coin me-1"></i>{{ __('ui.citizen_show_payment_required') }}</strong><br>
                <span>{{ __('ui.citizen_show_lbl_amount') }}: <strong>${{ number_format((float)($request->service?->price ?? 0), 2) }} USD</strong></span>
            </div>
            <a href="{{ route('citizen.requests.pay', $request) }}" class="btn-pay-now">
                <i class="bi bi-credit-card"></i> {{ __('ui.citizen_show_pay_now') }}
            </a>
        </div>
    @endif

    {{-- Page header --}}
    <div class="req-header">
        <div>
            <div class="req-title">{{ __('ui.request_hash', ['id' => $request->id]) }}</div>
            <p class="req-sub">{{ __('ui.citizen_show_qr_save') }}</p>
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
                    <div class="detail-card-title"><i class="bi bi-building"></i> {{ __('ui.citizen_show_card_service') }}</div>
                </div>
                <div class="detail-card-body">
                    <div class="info-row">
                        <div class="info-label">{{ __('ui.citizen_show_lbl_service') }}</div>
                        <div class="info-value">{{ $request->service?->name ?? '—' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">{{ __('ui.citizen_show_lbl_office') }}</div>
                        <div class="info-value">{{ $request->governmentOffice?->name ?? '—' }}</div>
                    </div>
                    @if($request->governmentOffice?->address)
                        <div class="info-row">
                            <div class="info-label">{{ __('ui.citizen_show_lbl_address') }}</div>
                            <div class="info-value">{{ $request->governmentOffice->address }}</div>
                        </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label">{{ __('ui.citizen_show_lbl_submitted') }}</div>
                        <div class="info-value">
                            {{ ($request->submitted_at ?? $request->created_at)?->format('M j, Y — H:i') ?? '—' }}
                        </div>
                    </div>
                    @if($request->notes)
                        <div class="info-row">
                            <div class="info-label">{{ __('ui.citizen_show_lbl_notes') }}</div>
                            <div class="info-value">{!! nl2br(e($request->notes)) !!}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Rejection / missing docs alerts --}}
            @if($request->status === 'rejected' && $request->rejection_reason)
                <div class="req-alert req-alert-danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <div><strong>{{ __('ui.citizen_show_step_rejected') }}:</strong> {{ $request->rejection_reason }}</div>
                </div>
            @endif
            @if($request->status === 'missing_documents' && $request->missing_docs_note)
                <div class="req-alert req-alert-warning">
                    <i class="bi bi-paperclip"></i>
                    <div><strong>{{ __('ui.citizen_show_step_missing') }}:</strong> {{ $request->missing_docs_note }}</div>
                </div>
            @endif

            {{-- Payment info --}}
            @if($request->payment)
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title"><i class="bi bi-wallet2"></i> {{ __('ui.citizen_show_card_payment') }}</div>
                        @if($request->payment->status === 'completed')
                            <span class="paid-badge"><i class="bi bi-check-circle-fill"></i> {{ __('ui.completed') }}</span>
                        @else
                            <span class="pay-badge"><i class="bi bi-clock"></i> {{ __('ui.citizen_show_payment_required') }}</span>
                        @endif
                    </div>
                    <div class="detail-card-body">
                        <div class="info-row">
                            <div class="info-label">{{ __('ui.citizen_show_lbl_amount') }}</div>
                            <div class="info-value">${{ number_format((float)$request->payment->amount, 2) }} USD</div>
                        </div>
                        @if($request->payment->status === 'completed' && $request->payment->paid_at)
                            <div class="info-row">
                                <div class="info-label">{{ __('ui.citizen_show_lbl_paid_on') }}</div>
                                <div class="info-value">{{ $request->payment->paid_at->format('M j, Y H:i') }}</div>
                            </div>
                        @endif
                        @if($request->payment->method && $request->payment->method !== 'pending')
                            <div class="info-row">
                                <div class="info-label">{{ __('ui.citizen_show_lbl_method') }}</div>
                                <div class="info-value">{{ ucfirst($request->payment->method) }}</div>
                            </div>
                        @endif
                        @if($request->payment->transaction_id)
                            <div class="info-row">
                                <div class="info-label">{{ __('ui.citizen_show_lbl_transaction') }}</div>
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
                    <div class="detail-card-title"><i class="bi bi-paperclip"></i> {{ __('ui.citizen_show_card_documents') }}</div>
                    <span class="total-badge" style="background:#ede9fe;color:#6d28d9;font-size:.72rem;font-weight:700;padding:.15rem .55rem;border-radius:999px;">
                        {{ $request->documents->count() }}
                    </span>
                </div>

                @if($request->documents->isEmpty())
                    <div class="no-docs">
                        <i class="bi bi-file-earmark"></i>
                        {{ __('ui.office_req_show_no_docs') }}
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
                                        · {{ $doc->uploaded_by === 'office' ? __('ui.citizen_show_doc_by_office') : __('ui.citizen_show_doc_by_you') }}
                                        @if($doc->description) · {{ $doc->description }} @endif
                                    </div>
                                </div>
                            </div>
                            <div class="doc-actions">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn-doc btn-doc-open">
                                    <i class="bi bi-box-arrow-up-right"></i> {{ __('ui.citizen_show_doc_open') }}
                                </a>
                                <a href="{{ route('citizen.requests.documents.download', [$request, $doc]) }}" class="btn-doc btn-doc-dl">
                                    <i class="bi bi-download"></i> {{ __('ui.citizen_show_doc_download') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if($canUploadFollowupDocuments ?? false)
                    <div class="upload-section">
                        <div class="upload-title"><i class="bi bi-upload me-1" style="color:#a78bfa;"></i>{{ __('ui.citizen_show_upload_title') }}</div>
                        <div class="upload-sub">{{ __('ui.citizen_show_upload_hint') }}</div>
                        <form method="POST" action="{{ route('citizen.requests.documents.store', $request) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#6d5b8e;">Files <span class="text-danger">*</span></label>
                                <input type="file" name="attachments[]" multiple required
                                       accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/*"
                                       class="form-control form-control-sm @error('attachments') is-invalid @enderror">
                                <div class="form-text" style="font-size:.75rem;">{{ __('ui.citizen_show_upload_hint') }}</div>
                                @error('attachments')
                                    <div class="invalid-feedback d-block"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                                @for($i = 0; $i < 15; $i++)
                                    @error('attachments.'.$i)
                                        <div class="invalid-feedback d-block mt-1" style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.5rem .75rem;">
                                            <i class="bi bi-exclamation-circle me-1"></i>
                                            <strong>File {{ $i + 1 }}:</strong> {{ $message }}
                                        </div>
                                    @enderror
                                @endfor
                            </div>
                            <div class="mb-3">
                                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#6d5b8e;">Note <span class="text-muted fw-normal">({{ __('ui.citizen_create_notes_optional') }})</span></label>
                                <input type="text" name="note" maxlength="500" value="{{ old('note') }}"
                                       class="form-control form-control-sm @error('note') is-invalid @enderror">
                                @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded"
                                 style="background:#f5f3ff;border:1px solid #ede9fe;font-size:.73rem;color:#6d28d9;">
                                <i class="bi bi-stars flex-shrink-0"></i>
                                <span>{{ __('ui.citizen_show_upload_ai') }}</span>
                            </div>
                            <button type="submit" class="btn-chat">
                                <i class="bi bi-upload"></i> {{ __('ui.citizen_show_upload_btn') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>

        </div>

        {{-- ═══ RIGHT COLUMN ═══ --}}
        <div class="col-lg-4">

            {{-- Live progress stepper --}}
            @php
                $steps = [
                    ['key' => 'pending',            'icon' => 'send',         'label' => __('ui.citizen_show_step_submitted'),  'sub' => __('ui.citizen_show_step_submitted_desc')],
                    ['key' => 'in_review',          'icon' => 'eye',          'label' => __('ui.citizen_show_step_review'),     'sub' => __('ui.citizen_show_step_review_desc')],
                    ['key' => 'missing_documents',  'icon' => 'paperclip',    'label' => __('ui.citizen_show_step_missing'),    'sub' => __('ui.citizen_show_step_missing_desc')],
                    ['key' => 'approved',           'icon' => 'check-circle', 'label' => __('ui.citizen_show_step_approved'),   'sub' => __('ui.citizen_show_step_approved_desc')],
                    ['key' => 'completed',          'icon' => 'patch-check',  'label' => __('ui.citizen_show_step_completed'),  'sub' => __('ui.citizen_show_step_completed_desc')],
                ];
                $terminalStatuses = ['rejected', 'completed'];
                $isTerminal       = in_array($request->status, $terminalStatuses);
                $isRejected       = $request->status === 'rejected';

                $normalFlow = ['pending', 'in_review', 'missing_documents', 'approved', 'completed'];
                $currentIdx = array_search($request->status, $normalFlow);
            @endphp
            <div class="detail-card" id="progress-card">
                <div class="detail-card-header">
                    <div class="detail-card-title">
                        <i class="bi bi-bar-chart-steps"></i> {{ __('ui.citizen_show_card_progress') }}
                        @if(! $isTerminal)
                            <span class="live-badge"><span class="dot"></span> {{ __('ui.citizen_show_card_live') }}</span>
                        @endif
                    </div>
                </div>
                <div class="progress-stepper" id="stepper-wrap">
                    @if($isRejected)
                        <div class="d-flex align-items-start gap-3">
                            <div class="stepper-dot bad"><i class="bi bi-x-circle"></i></div>
                            <div class="stepper-text">
                                <div class="stepper-label" style="color:#991b1b;">{{ __('ui.citizen_show_step_rejected') }}</div>
                                <div class="stepper-sublabel">{{ $request->rejection_reason ?? __('ui.citizen_show_step_rejected_desc') }}</div>
                            </div>
                        </div>
                    @else
                        <div class="stepper-track">
                            @foreach($steps as $i => $step)
                                @php
                                    $stepIdx   = $i;
                                    $isDone    = $currentIdx !== false && $stepIdx < $currentIdx;
                                    $isActive  = $currentIdx !== false && $stepIdx === $currentIdx;
                                    $isIdle    = $currentIdx === false || $stepIdx > $currentIdx;
                                    $isLast    = $i === count($steps) - 1;
                                    $dotClass  = $isDone ? 'done' : ($isActive ? 'active' : 'idle');
                                    if ($step['key'] === 'completed' && $isActive) { $dotClass = 'done'; }
                                    $connLit   = $currentIdx !== false && $stepIdx < $currentIdx;
                                @endphp
                                <div class="stepper-item" data-step="{{ $step['key'] }}">
                                    <div class="stepper-line-wrap">
                                        <div class="stepper-dot {{ $dotClass }}">
                                            @if($isDone)
                                                <i class="bi bi-check-lg"></i>
                                            @else
                                                <i class="bi bi-{{ $step['icon'] }}"></i>
                                            @endif
                                        </div>
                                        @if(! $isLast)
                                            <div class="stepper-connector {{ $connLit ? 'lit' : '' }}"></div>
                                        @endif
                                    </div>
                                    <div class="stepper-text">
                                        <div class="stepper-label {{ $isIdle && ! $isActive ? 'idle-label' : '' }}">
                                            {{ $step['label'] }}
                                        </div>
                                        <div class="stepper-sublabel {{ $isActive ? 'active-sub' : '' }}">
                                            {{ $step['sub'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Live chat --}}
            @if($request->governmentOffice && empty($awaitingPayment))
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="detail-card-title"><i class="bi bi-chat-dots"></i> {{ __('ui.citizen_show_card_live_chat') }}</div>
                    </div>
                    <div class="detail-card-body">
                        <p style="font-size:.82rem;color:#9d7ecf;margin-bottom:.85rem;">
                            {{ $request->governmentOffice->name }}
                        </p>
                        <a href="{{ route('citizen.offices.chat', $request->governmentOffice) }}" class="btn-chat w-100 justify-content-center">
                            <i class="bi bi-chat-dots"></i> {{ __('ui.citizen_show_card_live_chat') }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- QR tracking --}}
            <div class="detail-card">
                <div class="detail-card-header">
                    <div class="detail-card-title"><i class="bi bi-qr-code"></i> {{ __('ui.citizen_show_card_qr') }}</div>
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
                        <div class="detail-card-title"><i class="bi bi-star"></i> {{ __('ui.citizen_show_card_feedback') }}</div>
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
                                    <div class="office-reply-label">{{ __('ui.citizen_show_feedback_reply') }}</div>
                                    <div class="office-reply-text">{!! nl2br(e($request->feedback->office_reply)) !!}</div>
                                    @if($request->feedback->replied_at)
                                        <div class="office-reply-date">{{ $request->feedback->replied_at->format('M j, Y H:i') }}</div>
                                    @endif
                                </div>
                            @else
                                <p style="font-size:.8rem;color:#b4a0d4;margin:0;">{{ __('ui.citizen_show_feedback_no_reply') }}</p>
                            @endif
                        @else
                            <p style="font-size:.84rem;color:#9d7ecf;margin-bottom:.85rem;">
                                {{ __('ui.citizen_show_feedback_placeholder') }}
                            </p>
                            <a href="{{ route('citizen.feedback.request.create', $request) }}" class="btn-rate w-100 justify-content-center">
                                <i class="bi bi-star"></i> {{ __('ui.citizen_show_feedback_rate') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

@endsection

@push('scripts')
@if(! in_array($request->status, ['completed', 'rejected']))
<script>
(function () {
    const requestId = {{ $request->id }};

    const normalFlow = ['pending', 'in_review', 'missing_documents', 'approved', 'completed'];
    const stepMeta   = {
        pending:            { icon: 'send',         label: @json(__('ui.citizen_show_step_submitted')), sub: @json(__('ui.citizen_show_step_submitted_desc')) },
        in_review:          { icon: 'eye',          label: @json(__('ui.citizen_show_step_review')),    sub: @json(__('ui.citizen_show_step_review_desc')) },
        missing_documents:  { icon: 'paperclip',    label: @json(__('ui.citizen_show_step_missing')),   sub: @json(__('ui.citizen_show_step_missing_desc')) },
        approved:           { icon: 'check-circle', label: @json(__('ui.citizen_show_step_approved')),  sub: @json(__('ui.citizen_show_step_approved_desc')) },
        completed:          { icon: 'patch-check',  label: @json(__('ui.citizen_show_step_completed')), sub: @json(__('ui.citizen_show_step_completed_desc')) },
    };

    function buildStepper(status, rejectionReason) {
        const wrap = document.getElementById('stepper-wrap');
        if (! wrap) return;

        if (status === 'rejected') {
            wrap.innerHTML = `
                <div class="d-flex align-items-start gap-3 status-flash">
                    <div class="stepper-dot bad"><i class="bi bi-x-circle"></i></div>
                    <div class="stepper-text">
                        <div class="stepper-label" style="color:#991b1b;">@json(__('ui.citizen_show_step_rejected'))</div>
                        <div class="stepper-sublabel">${rejectionReason || '@json(__('ui.citizen_show_step_rejected_desc'))'}</div>
                    </div>
                </div>`;
            return;
        }

        const currentIdx = normalFlow.indexOf(status);
        wrap.innerHTML = '<div class="stepper-track">' + normalFlow.map((key, i) => {
            const step    = stepMeta[key];
            const isDone  = currentIdx > i;
            const isActive= currentIdx === i;
            const isIdle  = currentIdx < i;
            const isLast  = i === normalFlow.length - 1;
            let dotClass  = isDone ? 'done' : (isActive ? 'active' : 'idle');
            if (key === 'completed' && isActive) dotClass = 'done';

            const iconHtml   = isDone ? '<i class="bi bi-check-lg"></i>' : `<i class="bi bi-${step.icon}"></i>`;
            const connector  = isLast ? '' : `<div class="stepper-connector ${isDone ? 'lit' : ''}"></div>`;

            return `
            <div class="stepper-item ${isActive ? 'status-flash' : ''}" data-step="${key}">
                <div class="stepper-line-wrap">
                    <div class="stepper-dot ${dotClass}">${iconHtml}</div>
                    ${connector}
                </div>
                <div class="stepper-text">
                    <div class="stepper-label ${isIdle ? 'idle-label' : ''}">${step.label}</div>
                    <div class="stepper-sublabel ${isActive ? 'active-sub' : ''}">${step.sub}</div>
                </div>
            </div>`;
        }).join('') + '</div>';
    }

    function updateStatusPill(status) {
        const cfgMap = {
            pending:            { bg: '#fef3c7', color: '#92400e', icon: 'clock',        label: @json(__('ui.citizen_show_step_submitted')) },
            in_review:          { bg: '#dbeafe', color: '#1e40af', icon: 'eye',          label: @json(__('ui.citizen_show_step_review')) },
            missing_documents:  { bg: '#ffedd5', color: '#c2410c', icon: 'paperclip',   label: @json(__('ui.citizen_show_step_missing')) },
            approved:           { bg: '#d1fae5', color: '#065f46', icon: 'check-circle', label: @json(__('ui.citizen_show_step_approved')) },
            rejected:           { bg: '#fde8e8', color: '#991b1b', icon: 'x-circle',    label: @json(__('ui.citizen_show_step_rejected')) },
            completed:          { bg: '#ede9fe', color: '#4c1d95', icon: 'patch-check', label: @json(__('ui.citizen_show_step_completed')) },
        };
        const cfg = cfgMap[status] || { bg: '#f3f4f6', color: '#374151', icon: 'circle', label: status };

        document.querySelectorAll('.req-status-pill').forEach(el => {
            el.style.background = cfg.bg;
            el.style.color      = cfg.color;
            el.innerHTML        = `<i class="bi bi-${cfg.icon}"></i> ${cfg.label}`;
            el.classList.remove('status-flash');
            void el.offsetWidth;
            el.classList.add('status-flash');
        });

    }

    if (typeof window.Echo !== 'undefined') {
        window.Echo.private(`service-request.${requestId}`)
            .listen('.status.updated', function (data) {
                buildStepper(data.status, data.rejection_reason);
                updateStatusPill(data.status);
                if (data.status === 'completed' || data.status === 'rejected') {
                    setTimeout(() => window.location.reload(), 1800);
                }
            });
    }
})();
</script>
@endif
@endpush
