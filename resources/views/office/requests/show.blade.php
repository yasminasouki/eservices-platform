@extends('layouts.office')

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
                        <a href="{{ route('office.requests.index', $office) }}">Requests</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $request->id }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">Request #{{ $request->id }}</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        <span class="badge {{ $statusBadgeClass($request->status) }} fs-6">
            {{ $statusLabel($request->status) }}
        </span>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            @if($request->citizen)
                <div class="card card-soft mb-4 border-success border-opacity-25">
                    <div class="card-header bg-white border-0 fw-semibold d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span>Live chat</span>
                        <a href="{{ route('office.chat.show', [$office, $request->citizen]) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-chat-dots me-1"></i>Open thread with {{ $request->citizen->name }}
                        </a>
                    </div>
                    <div class="card-body small text-muted mb-0 py-3">
                        General live chat with this citizen (same thread as from the office chat inbox).
                    </div>
                </div>
            @endif

            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Citizen & service</div>
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
                        <dt class="col-sm-4 text-muted">Internal notes</dt>
                        <dd class="col-sm-8 mb-0">{!! $request->notes ? nl2br(e($request->notes)) : '—' !!}</dd>
                    </dl>
                </div>
            </div>

            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Documents</div>
                <div class="card-body p-0">
                    @if($request->documents->isEmpty())
                        <p class="text-muted small mb-0 p-3">No documents uploaded yet.</p>
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
                                    <a href="{{ Storage::url($doc->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success flex-shrink-0">
                                        <i class="bi bi-download me-1"></i>Open
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">Upload document (office)</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('office.requests.documents.store', [$office, $request]) }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label small">File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control form-control-sm" required>
                            <div class="form-text">Max 12 MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="certificate">Certificate</option>
                                <option value="generated">Generated</option>
                                <option value="receipt">Receipt</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Description</label>
                            <input type="text" name="description" class="form-control form-control-sm" maxlength="500" value="{{ old('description') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-upload me-1"></i>Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">Update status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('office.requests.status', [$office, $request]) }}" id="office-status-form">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label for="status" class="form-label small">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select form-select-sm" required>
                                @foreach(\App\Models\ServiceRequest::STATUSES as $s)
                                    <option value="{{ $s }}" @selected(old('status', $request->status) === $s)>
                                        {{ $statusLabel($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3" id="field-rejection" style="display: none;">
                            <label for="rejection_reason" class="form-label small">Rejection reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control form-control-sm" rows="3" maxlength="5000">{{ old('rejection_reason', $request->rejection_reason) }}</textarea>
                        </div>
                        <div class="mb-3" id="field-missing" style="display: none;">
                            <label for="missing_docs_note" class="form-label small">Missing documents note <span class="text-danger">*</span></label>
                            <textarea name="missing_docs_note" id="missing_docs_note" class="form-control form-control-sm" rows="3" maxlength="5000">{{ old('missing_docs_note', $request->missing_docs_note) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status_note" class="form-label small">Status log note <span class="text-muted">(optional)</span></label>
                            <input type="text" name="status_note" id="status_note" class="form-control form-control-sm" maxlength="2000" value="{{ old('status_note') }}">
                            <div class="form-text">Appended to the audit trail when you save.</div>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">Save status</button>
                    </form>
                </div>
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
    </script>
@endpush
