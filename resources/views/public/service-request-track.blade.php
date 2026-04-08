@extends('layouts.public-minimal')

@section('title', 'Request status — E-Services')

@php
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
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-2" style="width:56px;height:56px;">
                    <i class="bi bi-qr-code-scan fs-3"></i>
                </div>
                <h1 class="h4 fw-bold mb-1">Service request status</h1>
                <p class="text-muted small mb-0">Reference <span class="font-monospace">{{ $request->qr_code }}</span></p>
            </div>

            <div class="card public-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-4">
                        <div>
                            <div class="text-muted text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.04em;">Current status</div>
                            <span class="badge {{ $statusBadge($request->status) }} fs-6">{{ $statusLabel($request->status) }}</span>
                        </div>
                        <div class="text-end small text-muted">
                            <div>Updated {{ $request->updated_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>

                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Service</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->service?->name ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Office</dt>
                        <dd class="col-sm-8 mb-2">{{ $request->governmentOffice?->name ?? '—' }}</dd>
                        <dt class="col-sm-4 text-muted">Submitted</dt>
                        <dd class="col-sm-8 mb-0">{{ $request->submitted_at?->format('M j, Y g:i A') ?? $request->created_at?->format('M j, Y g:i A') }}</dd>
                    </dl>

                    @if($request->status === 'missing_documents')
                        <div class="alert alert-warning small mt-4 mb-0">
                            <i class="bi bi-file-earmark-plus me-1"></i>
                            This request needs additional documents. Sign in to your citizen account to see details and upload files.
                        </div>
                    @elseif($request->status === 'rejected')
                        <div class="alert alert-danger small mt-4 mb-0">
                            <i class="bi bi-x-circle me-1"></i>
                            This request was not approved. Sign in for the full reason and next steps.
                        </div>
                    @elseif($request->status === 'completed')
                        <div class="alert alert-success small mt-4 mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            This request is completed. Sign in to download documents or leave feedback.
                        </div>
                    @else
                        <p class="small text-muted mt-4 mb-0">
                            This page shows status only. For documents, messages, and personal details, open your citizen account.
                        </p>
                    @endif
                </div>
            </div>

            <p class="text-center small text-muted mt-4 mb-0">
                <a href="{{ route('login') }}" class="text-decoration-none">Citizen login</a>
                <span class="mx-2">·</span>
                <a href="{{ url('/') }}" class="text-decoration-none">Home</a>
            </p>
        </div>
    </div>
@endsection
