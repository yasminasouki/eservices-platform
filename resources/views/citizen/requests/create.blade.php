@extends('layouts.citizen')

@section('title', 'Apply · '.$service->name)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.index') }}">Offices</a></li>
            <li class="breadcrumb-item"><a href="{{ route('citizen.offices.show', $office) }}">{{ $office->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Apply</li>
        </ol>
    </nav>

    <h2 class="fw-bold mb-1">Request: {{ $service->name }}</h2>
    <p class="text-muted small mb-4">{{ $office->name }}</p>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">Service details</div>
                <div class="card-body small">
                    @if($service->description)
                        <p class="mb-3">{{ $service->description }}</p>
                    @endif
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">Fee</dt>
                        <dd class="col-7 mb-2">
                            @if((float) $service->price > 0)
                                <strong>${{ number_format((float) $service->price, 2) }} USD</strong>
                                <div class="text-muted mt-1" style="font-size:0.75rem;">After you submit, you will pay online (card via Stripe or cryptocurrency) before the office receives this request.</div>
                            @else
                                <span class="badge bg-success">No fee</span>
                            @endif
                        </dd>
                        @if($service->duration)
                            <dt class="col-5 text-muted">Duration</dt>
                            <dd class="col-7 mb-2">{{ $service->duration }} {{ $service->duration_unit }}</dd>
                        @endif
                    </dl>
                    @if($service->required_documents && count($service->required_documents) > 0)
                        <div class="mt-3 pt-3 border-top">
                            <div class="fw-semibold mb-2">Documents to upload</div>
                            <ul class="mb-0 ps-3">
                                @foreach($service->required_documents as $doc)
                                    <li>{{ $doc }}</li>
                                @endforeach
                            </ul>
                            <p class="text-muted mt-2 mb-0">Each document has its own upload field on the right.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card card-soft">
                <div class="card-header bg-white border-0 fw-semibold">Your application</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('citizen.services.apply.store', [$office, $service]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="notes" class="form-label small">Notes for the office <span class="text-muted">(optional)</span></label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" maxlength="5000">{{ old('notes') }}</textarea>
                        </div>
                        @php
                            $requiredDocs = $service->required_documents ?? [];
                            $requiredN = count($requiredDocs);
                        @endphp
                        @if($requiredN > 0)
                            <div class="mb-3">
                                <div class="form-label small fw-semibold">
                                    Required documents <span class="text-danger">*</span>
                                </div>
                                @foreach($requiredDocs as $index => $docLabel)
                                    <div class="mb-3">
                                        <label class="form-label small mb-1" for="attachment_slot_{{ $index }}">{{ $docLabel }}</label>
                                        <input type="file"
                                               name="attachments[]"
                                               id="attachment_slot_{{ $index }}"
                                               class="form-control @error('attachments.'.$index) is-invalid @enderror"
                                               required
                                               accept=".pdf,.jpg,.jpeg,.png">
                                        @error('attachments.'.$index)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                                <div class="form-text">Max 12 MB per file. PDF or images.</div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="attachments" class="form-label small">
                                    Attachments <span class="text-muted">(optional)</span>
                                </label>
                                <input type="file" name="attachments[]" id="attachments" class="form-control" multiple>
                                <div class="form-text">Max 12 MB per file. Up to 15 files.</div>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">Submit request</button>
                        <a href="{{ route('citizen.offices.show', $office) }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
