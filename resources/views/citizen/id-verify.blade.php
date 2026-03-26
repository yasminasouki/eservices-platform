<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Verification — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .drop-zone {
            border: 2px dashed #9c27b0;
            border-radius: 10px;
            background: #faf5ff;
            cursor: pointer;
            transition: background .2s;
            min-height: 160px;
        }
        .drop-zone:hover, .drop-zone.drag-over { background: #f3e5f5; border-color: #6a1b9a; }
        .preview-wrapper { display: none; }
        .preview-wrapper img { max-height: 150px; object-fit: contain; border-radius: 8px; border: 1px solid #e0e0e0; }
        .section-label {
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #7b1fa2;
            border-bottom: 2px solid #ede7f6;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
    </style>
</head>
<body class="bg-light">

    {{-- ── Navbar ── --}}
    <nav class="navbar navbar-dark px-3" style="background: linear-gradient(135deg, #4a148c, #6a1b9a);">
        <span class="navbar-brand fw-bold">
            <i class="bi bi-building-fill-gear me-2"></i>E-Services Platform
        </span>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('citizen.dashboard') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
            <span class="text-white small">{{ auth()->user()->name }}</span>
            <span class="badge bg-light text-dark">Citizen</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                {{-- ── Flash messages ── --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- ── Main card ── --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">

                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-person-vcard me-2" style="color:#7b1fa2"></i>
                            Identity Verification
                        </h4>
                        <p class="text-muted small mb-4">
                            Upload a clear photo or scan of <strong>both sides</strong> of your Lebanese national ID.
                            All information will be extracted automatically.
                        </p>

                        {{-- ── Upload form ── --}}
                        <form method="POST"
                              action="{{ route('citizen.id.upload') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="row g-4 mb-4">

                                {{-- Front side --}}
                                <div class="col-md-6">
                                    <p class="section-label">
                                        <i class="bi bi-credit-card me-1"></i>Front Side — الوجه الأمامي
                                    </p>
                                    <div class="drop-zone p-3 text-center"
                                         id="dropZoneFront"
                                         onclick="document.getElementById('id_document_front').click()">
                                        <i class="bi bi-cloud-arrow-up fs-2" style="color:#9c27b0"></i>
                                        <p class="mb-1 fw-semibold small">Click or drag to upload</p>
                                        <p class="text-muted mb-0" style="font-size:.75rem">JPG, PNG, PDF — max 5 MB</p>
                                    </div>
                                    <input type="file"
                                           id="id_document_front"
                                           name="id_document_front"
                                           accept=".jpg,.jpeg,.png,.pdf"
                                           class="d-none @error('id_document_front') is-invalid @enderror">
                                    @error('id_document_front')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="preview-wrapper text-center mt-2" id="previewFrontWrapper">
                                        <img id="previewFrontImg" src="" alt="Front preview">
                                        <p id="previewFrontName" class="text-muted small mt-1 mb-0"></p>
                                    </div>
                                </div>

                                {{-- Back side --}}
                                <div class="col-md-6">
                                    <p class="section-label">
                                        <i class="bi bi-credit-card-2-back me-1"></i>Back Side — الوجه الخلفي
                                    </p>
                                    <div class="drop-zone p-3 text-center"
                                         id="dropZoneBack"
                                         onclick="document.getElementById('id_document_back').click()">
                                        <i class="bi bi-cloud-arrow-up fs-2" style="color:#9c27b0"></i>
                                        <p class="mb-1 fw-semibold small">Click or drag to upload</p>
                                        <p class="text-muted mb-0" style="font-size:.75rem">JPG, PNG, PDF — max 5 MB</p>
                                    </div>
                                    <input type="file"
                                           id="id_document_back"
                                           name="id_document_back"
                                           accept=".jpg,.jpeg,.png,.pdf"
                                           class="d-none @error('id_document_back') is-invalid @enderror">
                                    @error('id_document_back')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="preview-wrapper text-center mt-2" id="previewBackWrapper">
                                        <img id="previewBackImg" src="" alt="Back preview">
                                        <p id="previewBackName" class="text-muted small mt-1 mb-0"></p>
                                    </div>
                                </div>

                            </div>

                            <button type="submit" class="btn w-100 text-white" style="background:#7b1fa2;border-color:#7b1fa2">
                                <i class="bi bi-cpu me-2"></i>Upload &amp; Extract
                            </button>
                        </form>

                        {{-- ── Extracted data (shown after upload) ── --}}
                        @if($verification)
                            <hr class="my-4">

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-semibold mb-0">Extracted Information</h6>
                                @php
                                    $statusMap = [
                                        'pending'  => ['bg-warning text-dark', 'clock-history', 'Pending Review'],
                                        'verified' => ['bg-success text-white', 'check-circle',  'Verified'],
                                        'rejected' => ['bg-danger text-white',  'x-circle',      'Rejected'],
                                    ];
                                    [$badgeClass, $icon, $label] = $statusMap[$verification->status]
                                        ?? ['bg-secondary text-white', 'question-circle', ucfirst($verification->status)];
                                @endphp
                                <span class="badge {{ $badgeClass }} px-3 py-2">
                                    <i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}
                                </span>
                            </div>

                            @php
                                $nameParts  = explode(' ', $verification->extracted_name ?? '', 2);
                                $firstName  = $nameParts[0] ?? '';
                                $lastName   = $nameParts[1] ?? '';
                                $fmtDate    = fn($d) => $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : ($d ?? '');
                                $dob        = $fmtDate($verification->extracted_dob);
                                $issueDate  = $fmtDate($verification->extracted_issue_date);
                                $expiryDate = $fmtDate($verification->extracted_expiry_date);
                            @endphp

                            <form method="POST" action="{{ route('citizen.id.save') }}">
                                @csrf

                                {{-- ─── Front side fields ─── --}}
                                <p class="section-label mt-2">
                                    <i class="bi bi-credit-card me-1"></i>Front Side — Personal Info
                                </p>
                                <div class="row g-3 mb-4">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">First Name — الاسم</label>
                                        <input type="text" name="first_name" class="form-control" value="{{ $firstName }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Last Name — الشهرة</label>
                                        <input type="text" name="last_name" class="form-control" value="{{ $lastName }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Father's Name — اسم الأب</label>
                                        <input type="text" name="father_name" class="form-control" value="{{ $verification->extracted_father_name }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Mother's Name — اسم الأم</label>
                                        <input type="text" name="mother_name" class="form-control" value="{{ $verification->extracted_mother_name }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Date of Birth — تاريخ الولادة</label>
                                        <input type="text" name="dob" class="form-control" value="{{ $dob }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Place of Birth — مكان الولادة</label>
                                        <input type="text" name="place_of_birth" class="form-control" value="{{ $verification->extracted_place_of_birth }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">Gender — الجنس</label>
                                        <input type="text" name="gender" class="form-control" value="{{ $verification->extracted_gender }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">Blood Type — فصيلة الدم</label>
                                        <input type="text" name="blood_type" class="form-control" value="{{ $verification->extracted_blood_type }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">Marital Status — الحالة الاجتماعية</label>
                                        <input type="text" name="marital_status" class="form-control" value="{{ $verification->extracted_marital_status }}">
                                    </div>
                                </div>

                                {{-- ─── Back side fields ─── --}}
                                <p class="section-label">
                                    <i class="bi bi-credit-card-2-back me-1"></i>Back Side — Document Info
                                </p>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">ID Number — الرقم</label>
                                        <input type="text" name="id_number" class="form-control" value="{{ $verification->extracted_id_number }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Registry Number — رقم السجل</label>
                                        <input type="text" name="registry_number" class="form-control" value="{{ $verification->extracted_registry_number }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">Locality — البلدة</label>
                                        <input type="text" name="locality" class="form-control" value="{{ $verification->extracted_locality }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">District — القضاء</label>
                                        <input type="text" name="district" class="form-control" value="{{ $verification->extracted_district }}">
                                    </div>
                                    <div class="col-sm-4">
                                        <label class="form-label small fw-semibold text-muted">Governorate — المحافظة</label>
                                        <input type="text" name="governorate" class="form-control" value="{{ $verification->extracted_governorate }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Issue Date — تاريخ الإصدار</label>
                                        <input type="text" name="issue_date" class="form-control" value="{{ $issueDate }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-muted">Expiry Date — تاريخ الانتهاء</label>
                                        <input type="text" name="expiry_date" class="form-control" value="{{ $expiryDate }}">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn w-100 text-white" style="background:#7b1fa2;border-color:#7b1fa2">
                                            <i class="bi bi-check-circle me-2"></i>Save &amp; Confirm
                                        </button>
                                    </div>
                                </div>

                            </form>

                            @if($verification->status === 'rejected')
                                <div class="alert alert-danger mt-3 mb-0 small">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Your document was rejected. Please upload clearer images and try again.
                                </div>
                            @elseif($verification->status === 'pending')
                                <p class="text-muted small mt-3 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Your documents are awaiting admin review. You will be notified once they are processed.
                                </p>
                            @endif
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setupDropZone(zoneId, inputId, previewWrapperId, previewImgId, previewNameId) {
            const zone    = document.getElementById(zoneId);
            const input   = document.getElementById(inputId);
            const wrapper = document.getElementById(previewWrapperId);
            const img     = document.getElementById(previewImgId);
            const name    = document.getElementById(previewNameId);

            input.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                name.textContent = file.name;
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = e => { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                    img.style.display = 'block';
                } else {
                    img.style.display = 'none';
                }
                wrapper.style.display = 'block';
            });

            zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
            zone.addEventListener('dragleave', ()  => zone.classList.remove('drag-over'));
            zone.addEventListener('drop', e => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }

        setupDropZone('dropZoneFront', 'id_document_front', 'previewFrontWrapper', 'previewFrontImg', 'previewFrontName');
        setupDropZone('dropZoneBack',  'id_document_back',  'previewBackWrapper',  'previewBackImg',  'previewBackName');
    </script>
</body>
</html>
