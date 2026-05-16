@extends('layouts.admin')

@section('title', 'Citizen — '.$user->name)

@section('content')
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb" class="small mb-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.citizens.index') }}">Citizen Accounts</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
            <p class="text-muted small mb-0">Citizen profile &amp; ID verification</p>
        </div>
        <a href="{{ route('admin.citizens.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Left column --}}
        <div class="col-lg-5">

            {{-- Citizen info card --}}
            <div class="card card-soft mb-4">
                <div class="card-header bg-white border-0 fw-semibold">
                    <i class="bi bi-person me-1"></i>Account info
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Name</dt>
                        <dd class="col-sm-8 mb-2">{{ $user->name }}</dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8 mb-2">{{ $user->email }}</dd>

                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8 mb-2">{{ $user->phone ?? '—' }}</dd>

                        <dt class="col-sm-4 text-muted">Account</dt>
                        <dd class="col-sm-8 mb-2">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">ID status</dt>
                        <dd class="col-sm-8 mb-0">
                            @php
                                $idStatus = $user->id_document_status ?? 'pending';
                                $idBadge  = match($idStatus) {
                                    'verified' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'pending'  => 'bg-warning text-dark',
                                    default    => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $idBadge }}">{{ ucfirst($idStatus) }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Approve / Reject actions --}}
            @if($verification)
                <div class="card card-soft">
                    <div class="card-header bg-white border-0 fw-semibold">
                        <i class="bi bi-shield-check me-1"></i>Verification decision
                    </div>
                    <div class="card-body">
                        @if($verification->status === 'pending')
                            <p class="small text-muted mb-3">Review the ID images and extracted data, then approve or reject this submission.</p>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('admin.citizens.approve-id', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg me-1"></i>Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bi bi-x-lg me-1"></i>Reject
                                </button>
                            </div>
                        @elseif($verification->status === 'verified')
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success fs-6"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                @if($verification->verified_at)
                                    <span class="small text-muted">{{ $verification->verified_at->format('M j, Y g:i a') }}</span>
                                @endif
                            </div>
                        @elseif($verification->status === 'rejected')
                            <span class="badge bg-danger fs-6"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- Right column — verification details --}}
        <div class="col-lg-7">
            @if($verification)

                {{-- ID images --}}
                <div class="card card-soft mb-4">
                    <div class="card-header bg-white border-0 fw-semibold">
                        <i class="bi bi-image me-1"></i>ID images
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <p class="small text-muted mb-1 fw-semibold">Front</p>
                                @if($verification->id_document_path)
                                    <a href="{{ url('/admin/id-documents/' . $verification->id_document_path) }}" target="_blank" rel="noopener">
                                        <img src="{{ url('/admin/id-documents/' . $verification->id_document_path) }}"
                                             alt="ID front"
                                             class="img-fluid rounded border"
                                             style="max-height:200px; object-fit:cover; width:100%;">
                                    </a>
                                @else
                                    <p class="text-muted small">Not uploaded.</p>
                                @endif
                            </div>
                            <div class="col-sm-6">
                                <p class="small text-muted mb-1 fw-semibold">Back</p>
                                @if($verification->id_document_back_path)
                                    <a href="{{ url('/admin/id-documents/' . $verification->id_document_back_path) }}" target="_blank" rel="noopener">
                                        <img src="{{ url('/admin/id-documents/' . $verification->id_document_back_path) }}"
                                             alt="ID back"
                                             class="img-fluid rounded border"
                                             style="max-height:200px; object-fit:cover; width:100%;">
                                    </a>
                                @else
                                    <p class="text-muted small">Not uploaded.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Extracted fields --}}
                <div class="card card-soft">
                    <div class="card-header bg-white border-0 fw-semibold">
                        <i class="bi bi-card-text me-1"></i>Extracted data
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle small">
                            <tbody>
                                @php
                                    $fields = [
                                        'Full Name'       => $verification->extracted_name,
                                        'Father Name'     => $verification->extracted_father_name,
                                        'Mother Name'     => $verification->extracted_mother_name,
                                        'Date of Birth'   => $verification->extracted_dob?->format('Y-m-d'),
                                        'Place of Birth'  => $verification->extracted_place_of_birth,
                                        'Gender'          => $verification->extracted_gender,
                                        'Blood Type'      => $verification->extracted_blood_type,
                                        'Marital Status'  => $verification->extracted_marital_status,
                                        'ID Number'       => $verification->extracted_id_number,
                                        'Registry Number' => $verification->extracted_registry_number,
                                        'Locality'        => $verification->extracted_locality,
                                        'District'        => $verification->extracted_district,
                                        'Governorate'     => $verification->extracted_governorate,
                                        'Issue Date'      => $verification->extracted_issue_date?->format('Y-m-d'),
                                        'Expiry Date'     => $verification->extracted_expiry_date?->format('Y-m-d'),
                                    ];
                                @endphp
                                @foreach($fields as $label => $value)
                                    <tr>
                                        <td class="text-muted ps-3" style="width:40%">{{ $label }}</td>
                                        <td class="pe-3">{{ $value ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <div class="alert alert-light border">
                    <i class="bi bi-info-circle me-1"></i>
                    This citizen has not submitted an ID verification request yet.
                </div>
            @endif
        </div>
    </div>

    {{-- Reject modal --}}
    @if($verification && $verification->status === 'pending')
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form method="POST" action="{{ route('admin.citizens.reject-id', $user) }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-semibold" id="rejectModalLabel">
                                <span class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center me-2" style="width:32px;height:32px">
                                    <i class="bi bi-x-lg text-danger"></i>
                                </span>
                                Reject ID verification
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body pt-0">
                            <p class="small text-muted mb-3">Provide a reason so the citizen knows what to correct and re-submit.</p>
                            <label for="reason" class="form-label fw-semibold small">Reason <span class="text-danger">*</span></label>
                            <textarea id="reason"
                                      name="reason"
                                      class="form-control @error('reason') is-invalid @enderror"
                                      rows="4"
                                      maxlength="500"
                                      placeholder="e.g. Image is blurry, ID appears expired…">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-lg me-1"></i>Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Re-open modal automatically if reason validation failed --}}
        @if($errors->has('reason'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    new bootstrap.Modal(document.getElementById('rejectModal')).show();
                });
            </script>
        @endif
    @endif
@endsection
