<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Verification — E-Services Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        #preview-wrapper { display: none; }
        #preview-wrapper img { max-height: 220px; object-fit: contain; border-radius: 8px; }
        .drop-zone {
            border: 2px dashed #9c27b0;
            border-radius: 10px;
            background: #faf5ff;
            cursor: pointer;
            transition: background .2s;
        }
        .drop-zone:hover { background: #f3e5f5; }
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
            <div class="col-lg-7">

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
                            Upload a clear photo or scan of your Lebanese national ID.
                            The system will extract your details automatically.
                        </p>

                        {{-- ── Upload form ── --}}
                        <form method="POST"
                              action="{{ route('citizen.id.upload') }}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="drop-zone p-4 text-center mb-3" id="dropZone"
                                 onclick="document.getElementById('id_document').click()">
                                <i class="bi bi-cloud-arrow-up fs-1" style="color:#9c27b0"></i>
                                <p class="mb-1 fw-semibold">Click to choose a file</p>
                                <p class="text-muted small mb-0">JPG, PNG, PDF — max 5 MB</p>
                            </div>

                            <input type="file"
                                   id="id_document"
                                   name="id_document"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="d-none @error('id_document') is-invalid @enderror">
                            @error('id_document')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            {{-- Image preview --}}
                            <div id="preview-wrapper" class="text-center mb-3">
                                <img id="preview-img" src="" alt="Preview">
                                <p id="preview-name" class="text-muted small mt-1 mb-0"></p>
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
                                        'approved' => ['bg-success text-white', 'check-circle',  'Approved'],
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
                                $nameParts = explode(' ', $verification->extracted_name ?? '', 2);
                                $firstName = $nameParts[0] ?? '';
                                $lastName  = $nameParts[1] ?? '';
                                $dob = $verification->extracted_dob instanceof \Carbon\Carbon
                                    ? $verification->extracted_dob->format('Y-m-d')
                                    : ($verification->extracted_dob ?? '');
                            @endphp

                            <form method="POST" action="{{ route('citizen.id.save') }}">
                                @csrf
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="{{ $firstName }}">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="{{ $lastName }}">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted">Date of Birth</label>
                                    <input type="text" name="dob" class="form-control" value="{{ $dob }}">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-semibold text-muted">ID Number</label>
                                    <input type="text" name="id_number" class="form-control" value="{{ $verification->extracted_id_number }}">
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
                                    Your document was rejected. Please upload a clearer image and try again.
                                </div>
                            @elseif($verification->status === 'pending')
                                <p class="text-muted small mt-3 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Your document is awaiting admin review. You will be notified once it is processed.
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
        const input       = document.getElementById('id_document');
        const dropZone    = document.getElementById('dropZone');
        const preview     = document.getElementById('preview-wrapper');
        const previewImg  = document.getElementById('preview-img');
        const previewName = document.getElementById('preview-name');

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            previewName.textContent = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = e => { previewImg.src = e.target.result; };
                reader.readAsDataURL(file);
                previewImg.style.display = 'block';
            } else {
                // PDF — just show filename, no image preview
                previewImg.style.display = 'none';
            }

            preview.style.display = 'block';
        });

        dropZone.addEventListener('dragover', e => {
            e.preventDefault();
            dropZone.style.background = '#f3e5f5';
        });
        dropZone.addEventListener('dragleave', () => {
            dropZone.style.background = '#faf5ff';
        });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.style.background = '#faf5ff';
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>
