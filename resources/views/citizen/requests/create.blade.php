@extends('layouts.citizen')

@section('title', 'Apply · '.$service->name)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">{{ __('ui.offices') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.show', $office) }}">{{ $office->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('ui.citizen_create_breadcrumb_apply') }}</li>
        </ol>
    </nav>

    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
            <div>
                <strong>{{ __('ui.please_fix_errors') }}</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <h2 class="fw-bold mb-1">{{ __('ui.request_hash', ['id' => '']) }} {{ $service->name }}</h2>
    <p class="text-muted small mb-4">{{ $office->name }}</p>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">{{ __('ui.citizen_create_service_details') }}</div>
                <div class="card-body small">
                    @if($service->description)
                        <p class="mb-3">{{ $service->description }}</p>
                    @endif
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">{{ __('ui.citizen_create_lbl_fee') }}</dt>
                        <dd class="col-7 mb-2">
                            @if((float) $service->price > 0)
                                <strong>${{ number_format((float) $service->price, 2) }} USD</strong>
                                <div class="text-muted mt-1" style="font-size:0.75rem;">{{ __('ui.citizen_create_fee_note') }}</div>
                            @else
                                <span class="badge bg-success">{{ __('ui.citizen_create_no_fee') }}</span>
                            @endif
                        </dd>
                        @if($service->duration)
                            <dt class="col-5 text-muted">{{ __('ui.citizen_create_lbl_duration') }}</dt>
                            <dd class="col-7 mb-2">{{ $service->duration }} {{ $service->duration_unit }}</dd>
                        @endif
                    </dl>
                    @if($service->required_documents && count($service->required_documents) > 0)
                        <div class="mt-3 pt-3 border-top">
                            <div class="fw-semibold mb-2">{{ __('ui.citizen_create_docs_title') }}</div>
                            <ul class="mb-0 ps-3">
                                @foreach($service->required_documents as $doc)
                                    <li>{{ $doc }}</li>
                                @endforeach
                            </ul>
                            <p class="text-muted mt-2 mb-0">{{ __('ui.citizen_create_docs_note') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">{{ __('ui.citizen_create_application') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('citizen.services.apply.store', [$office, $service]) }}" enctype="multipart/form-data" id="apply-form">
                        @csrf
                        <div class="mb-3">
                            <label for="notes" class="form-label small">{{ __('ui.citizen_create_notes_label') }} <span class="text-muted">{{ __('ui.citizen_create_notes_optional') }}</span></label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" maxlength="5000">{{ old('notes') }}</textarea>
                        </div>

                        @php
                            $requiredDocs = $service->required_documents ?? [];
                            $requiredN = count($requiredDocs);
                        @endphp

                        @if($requiredN > 0)
                            <div class="mb-3">
                                <div class="form-label small fw-semibold">
                                    {{ __('ui.citizen_create_req_docs') }} <span class="text-danger">*</span>
                                </div>
                                @foreach($requiredDocs as $index => $docLabel)
                                    <div class="mb-3">
                                        <label class="form-label small mb-1" for="attachment_slot_{{ $index }}">
                                            {{ $docLabel }}
                                        </label>
                                        <input type="file"
                                               name="attachments[]"
                                               id="attachment_slot_{{ $index }}"
                                               class="form-control @error('attachments.'.$index) is-invalid @enderror"
                                               required
                                               accept=".pdf,.jpg,.jpeg,.png">
                                        @error('attachments.'.$index)
                                            <div class="invalid-feedback d-block">
                                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                @endforeach
                                <div class="form-text">{{ __('ui.citizen_create_attach_hint') }}</div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="attachments" class="form-label small">
                                    {{ __('ui.citizen_create_attachments') }} <span class="text-muted">{{ __('ui.citizen_create_attach_optional') }}</span>
                                </label>
                                <input type="file" name="attachments[]" id="attachments"
                                       class="form-control @error('attachments') is-invalid @enderror" multiple
                                       accept=".pdf,.jpg,.jpeg,.png">
                                @for($i = 0; $i < 15; $i++)
                                    @error('attachments.'.$i)
                                        <div class="invalid-feedback d-block">
                                            <i class="bi bi-exclamation-circle me-1"></i>File {{ $i + 1 }}: {{ $message }}
                                        </div>
                                    @enderror
                                @endfor
                                @error('attachments')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <div class="form-text">{{ __('ui.citizen_create_attach_hint_multi') }}</div>
                            </div>
                        @endif

<button type="submit" class="btn btn-primary" id="submit-btn">
                            <span id="submit-label">{{ __('ui.citizen_create_submit') }}</span>
                            <span id="submit-spinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                {{ __('ui.citizen_create_submitting') }}
                            </span>
                        </button>
                        <a href="{{ route('citizen.offices.show', $office) }}" class="btn btn-outline-secondary ms-2">{{ __('ui.citizen_create_cancel') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('apply-form').addEventListener('submit', function () {
        const btn    = document.getElementById('submit-btn');
        const label  = document.getElementById('submit-label');
        const spinner = document.getElementById('submit-spinner');
        btn.disabled = true;
        label.classList.add('d-none');
        spinner.classList.remove('d-none');
    });
</script>
@endpush
