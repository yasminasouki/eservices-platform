@extends('layouts.citizen')

@section('title', 'Identity Verification')

@push('styles')
<style>
    .drop-zone {
        border: 2px dashed #c4b5fd;
        border-radius: 12px;
        background: #f8f5ff;
        cursor: pointer;
        transition: background .2s, border-color .2s;
        min-height: 160px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .3rem;
    }
    .drop-zone:hover, .drop-zone.drag-over {
        background: #ede9fe;
        border-color: #a78bfa;
    }
    .drop-zone .dz-icon {
        width: 48px; height: 48px;
        background: #ede9fe;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #8b5cf6;
        font-size: 1.3rem;
        margin-bottom: .25rem;
    }
    .drop-zone .dz-title { font-size: .88rem; font-weight: 700; color: #3b0764; }
    .drop-zone .dz-hint  { font-size: .74rem; color: #b4a0d4; }

    /* Drop zone error state */
    .drop-zone.dz-error {
        border-color: #fca5a5;
        background: #fff5f5;
    }
    .drop-zone.dz-error .dz-icon { background: #fee2e2; color: #dc2626; }
    .drop-zone.dz-success {
        border-color: #6ee7b7;
        background: #f0fdf4;
    }

    /* Inline error message */
    .dz-error-msg {
        display: none;
        align-items: center;
        gap: .4rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: .5rem .75rem;
        font-size: .8rem;
        color: #dc2626;
        font-weight: 500;
        margin-top: .5rem;
    }
    .dz-error-msg.show { display: flex; }
    .dz-error-msg i { font-size: .9rem; flex-shrink: 0; }

    /* Preview */
    .preview-wrapper { display: none; margin-top: .75rem; position: relative; }
    .preview-wrapper img {
        max-height: 140px;
        width: 100%;
        object-fit: contain;
        border-radius: 10px;
        border: 1px solid #e0d9ff;
        display: block;
    }
    .preview-side-label {
        position: absolute;
        top: 8px; left: 8px;
        background: rgba(124,58,237,0.85);
        color: #fff;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: .2rem .55rem;
        border-radius: 5px;
        backdrop-filter: blur(4px);
    }
    .preview-remove {
        position: absolute;
        top: 6px; right: 6px;
        width: 24px; height: 24px;
        background: rgba(0,0,0,0.5);
        border: none;
        border-radius: 50%;
        color: #fff;
        font-size: .7rem;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background .15s;
        padding: 0;
    }
    .preview-remove:hover { background: #dc2626; }
    .preview-name { font-size: .75rem; color: #9d7ecf; text-align: center; margin-top: .4rem; }

    /* Swap button */
    .swap-row {
        display: none;
        justify-content: center;
        margin: -.5rem 0 .5rem;
    }
    .swap-row.show { display: flex; }
    .swap-btn {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #c2410c;
        font-size: .78rem;
        font-weight: 700;
        padding: .35rem .9rem;
        border-radius: 999px;
        cursor: pointer;
        transition: background .15s;
    }
    .swap-btn:hover { background: #ffedd5; }

    /* Submit disabled */
    .submit-btn:disabled { background: #e5e7eb; color: #9ca3af; cursor: not-allowed; }

    .section-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #8b5cf6;
        border-bottom: 2px solid #ede9fe;
        padding-bottom: 6px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .id-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(124, 58, 237, 0.07);
        border: 1px solid #f0ecff;
        overflow: hidden;
    }
    .id-card-header {
        background: linear-gradient(135deg, #f3f0ff, #ede9fe);
        border-bottom: 1px solid #e0d9ff;
        padding: 1.5rem 1.75rem 1.25rem;
    }
    .id-card-body { padding: 1.75rem; }

    .submit-btn {
        width: 100%;
        background: #c4b5fd;
        border: none;
        color: #3b0764;
        font-weight: 700;
        font-size: .95rem;
        padding: .75rem;
        border-radius: 10px;
        transition: background .15s;
        display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .submit-btn:hover { background: #a78bfa; color: #3b0764; }

    /* Status badges */
    .status-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        padding: .4rem .9rem;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
    }
    .status-badge.pending  { background: #fef3c7; color: #92400e; }
    .status-badge.verified { background: #d1fae5; color: #065f46; }
    .status-badge.rejected { background: #fde8e8; color: #991b1b; }

    /* Form fields */
    .form-label {
        font-size: .78rem;
        font-weight: 600;
        color: #6d5b8e;
        margin-bottom: .35rem;
    }
    .form-control {
        border-color: #e0d9ff;
        border-radius: 8px;
        font-size: .88rem;
        color: #1f1235;
    }
    .form-control:focus {
        border-color: #c4b5fd;
        box-shadow: 0 0 0 .2rem rgba(139, 92, 246, 0.1);
    }

    .extracted-section {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(124, 58, 237, 0.07);
        border: 1px solid #f0ecff;
        padding: 1.5rem 1.75rem;
        margin-top: 1.5rem;
    }
    .extracted-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.25rem;
        padding-bottom: .85rem;
        border-bottom: 1px solid #f3f0ff;
    }
    .extracted-title { font-weight: 700; font-size: 1rem; color: #1f1235; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">


        {{-- Upload card --}}
        <div class="id-card">
            <div class="id-card-header">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <div style="width:38px;height:38px;background:#ddd6fe;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#7c3aed;font-size:1.1rem;">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <h5 class="fw-bold mb-0" style="color:#1f1235;">Identity Verification</h5>
                </div>
                <p class="text-muted small mb-0" style="padding-left:50px;">
                    Upload a clear photo or scan of <strong>both sides</strong> of your Lebanese national ID.
                    Information will be extracted automatically.
                </p>
            </div>

            <div class="id-card-body">
                <form method="POST" action="{{ route('citizen.id.upload') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-2">
                        {{-- Front side --}}
                        <div class="col-md-6">
                            <p class="section-label">
                                <i class="bi bi-credit-card"></i> Front Side — الوجه الأمامي
                            </p>
                            <div class="drop-zone" id="dropZoneFront"
                                 onclick="document.getElementById('id_document_front').click()">
                                <div class="dz-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="dz-title">Click or drag to upload</div>
                                <div class="dz-hint">JPG, PNG, PDF — max 5 MB</div>
                            </div>
                            <input type="file" id="id_document_front" name="id_document_front"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="d-none @error('id_document_front') is-invalid @enderror">
                            <div class="dz-error-msg" id="errorFront">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span id="errorFrontText"></span>
                            </div>
                            @error('id_document_front')
                                <div class="dz-error-msg show"><i class="bi bi-exclamation-circle-fill"></i><span>{{ $message }}</span></div>
                            @enderror
                            <div class="preview-wrapper" id="previewFrontWrapper">
                                <span class="preview-side-label">Front</span>
                                <img id="previewFrontImg" src="" alt="Front preview">
                                <button type="button" class="preview-remove" onclick="clearSide('front')" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                                <div class="preview-name" id="previewFrontName"></div>
                            </div>
                        </div>

                        {{-- Back side --}}
                        <div class="col-md-6">
                            <p class="section-label">
                                <i class="bi bi-credit-card-2-back"></i> Back Side — الوجه الخلفي
                            </p>
                            <div class="drop-zone" id="dropZoneBack"
                                 onclick="document.getElementById('id_document_back').click()">
                                <div class="dz-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="dz-title">Click or drag to upload</div>
                                <div class="dz-hint">JPG, PNG, PDF — max 5 MB</div>
                            </div>
                            <input type="file" id="id_document_back" name="id_document_back"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="d-none @error('id_document_back') is-invalid @enderror">
                            <div class="dz-error-msg" id="errorBack">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span id="errorBackText"></span>
                            </div>
                            @error('id_document_back')
                                <div class="dz-error-msg show"><i class="bi bi-exclamation-circle-fill"></i><span>{{ $message }}</span></div>
                            @enderror
                            <div class="preview-wrapper" id="previewBackWrapper">
                                <span class="preview-side-label">Back</span>
                                <img id="previewBackImg" src="" alt="Back preview">
                                <button type="button" class="preview-remove" onclick="clearSide('back')" title="Remove">
                                    <i class="bi bi-x"></i>
                                </button>
                                <div class="preview-name" id="previewBackName"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Swap hint (shown when both uploaded) --}}
                    <div class="swap-row mb-3" id="swapRow">
                        <button type="button" class="swap-btn" onclick="swapSides()">
                            <i class="bi bi-arrow-left-right"></i> Mixed up front &amp; back? Swap them
                        </button>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="bi bi-cpu"></i> Upload &amp; Extract
                    </button>
                </form>
            </div>
        </div>

        {{-- Extracted data --}}
        @if($verification)
            <div class="extracted-section">
                <div class="extracted-header">
                    <div class="extracted-title">Extracted Information</div>
                    @php
                        $statusMap = [
                            'pending'  => ['pending',  'clock-history', 'Pending Review'],
                            'verified' => ['verified', 'check-circle',  'Verified'],
                            'rejected' => ['rejected', 'x-circle',      'Rejected'],
                        ];
                        [$sClass, $sIcon, $sLabel] = $statusMap[$verification->status]
                            ?? ['pending', 'question-circle', ucfirst($verification->status)];
                    @endphp
                    <span class="status-badge {{ $sClass }}">
                        <i class="bi bi-{{ $sIcon }}"></i> {{ $sLabel }}
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

                    <p class="section-label"><i class="bi bi-credit-card"></i> Front Side — Personal Info</p>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label">First Name — الاسم</label>
                            <input type="text" name="first_name" class="form-control" value="{{ $firstName }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Last Name — الشهرة</label>
                            <input type="text" name="last_name" class="form-control" value="{{ $lastName }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Father's Name — اسم الأب</label>
                            <input type="text" name="father_name" class="form-control" value="{{ $verification->extracted_father_name }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Mother's Name — اسم الأم</label>
                            <input type="text" name="mother_name" class="form-control" value="{{ $verification->extracted_mother_name }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Date of Birth — تاريخ الولادة</label>
                            <input type="text" name="dob" class="form-control" value="{{ $dob }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Place of Birth — مكان الولادة</label>
                            <input type="text" name="place_of_birth" class="form-control" value="{{ $verification->extracted_place_of_birth }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Gender — الجنس</label>
                            <input type="text" name="gender" class="form-control" value="{{ $verification->extracted_gender }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Blood Type — فصيلة الدم</label>
                            <input type="text" name="blood_type" class="form-control" value="{{ $verification->extracted_blood_type }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Marital Status — الحالة الاجتماعية</label>
                            <input type="text" name="marital_status" class="form-control" value="{{ $verification->extracted_marital_status }}">
                        </div>
                    </div>

                    <p class="section-label"><i class="bi bi-credit-card-2-back"></i> Back Side — Document Info</p>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">ID Number — الرقم</label>
                            <input type="text" name="id_number" class="form-control" value="{{ $verification->extracted_id_number }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Registry Number — رقم السجل</label>
                            <input type="text" name="registry_number" class="form-control" value="{{ $verification->extracted_registry_number }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Locality — البلدة</label>
                            <input type="text" name="locality" class="form-control" value="{{ $verification->extracted_locality }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">District — القضاء</label>
                            <input type="text" name="district" class="form-control" value="{{ $verification->extracted_district }}">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Governorate — المحافظة</label>
                            <input type="text" name="governorate" class="form-control" value="{{ $verification->extracted_governorate }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Issue Date — تاريخ الإصدار</label>
                            <input type="text" name="issue_date" class="form-control" value="{{ $issueDate }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Expiry Date — تاريخ الانتهاء</label>
                            <input type="text" name="expiry_date" class="form-control" value="{{ $expiryDate }}">
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="submit-btn">
                                <i class="bi bi-check-circle"></i> Save &amp; Confirm
                            </button>
                        </div>
                    </div>
                </form>

                @if($verification->status === 'rejected')
                    <div class="alert alert-danger d-flex align-items-center gap-2 mt-4 mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span class="small">Your document was rejected. Please upload clearer images and try again.</span>
                    </div>
                @elseif($verification->status === 'pending')
                    <div class="alert alert-info d-flex align-items-center gap-2 mt-4 mb-0">
                        <i class="bi bi-info-circle-fill"></i>
                        <span class="small">Your documents are awaiting admin review. You will be notified once they are processed.</span>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
const MAX_SIZE = 5 * 1024 * 1024; // 5 MB
const ALLOWED  = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];

// State: store DataTransfer objects so we can swap
const state = { front: null, back: null };

function showError(side, msg) {
    const el   = document.getElementById('error' + cap(side));
    const text = document.getElementById('error' + cap(side) + 'Text');
    const zone = document.getElementById('dropZone' + cap(side));
    text.textContent = msg;
    el.classList.add('show');
    zone.classList.add('dz-error');
    zone.classList.remove('dz-success');
}

function clearError(side) {
    const el   = document.getElementById('error' + cap(side));
    const zone = document.getElementById('dropZone' + cap(side));
    el.classList.remove('show');
    zone.classList.remove('dz-error');
}

function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function validateFile(file, side) {
    clearError(side);
    if (!ALLOWED.includes(file.type)) {
        showError(side, 'Invalid file type. Please upload a JPG, PNG, or PDF.');
        return false;
    }
    if (file.size > MAX_SIZE) {
        showError(side, 'File is too large. Maximum size is 5 MB.');
        return false;
    }
    return true;
}

function checkDuplicate() {
    const f = document.getElementById('id_document_front').files[0];
    const b = document.getElementById('id_document_back').files[0];
    const swapRow = document.getElementById('swapRow');
    if (f && b && f.name === b.name && f.size === b.size && f.lastModified === b.lastModified) {
        showError('front', 'You uploaded the same file for both sides. Please upload the correct front side.');
        showError('back',  'You uploaded the same file for both sides. Please upload the correct back side.');
        document.getElementById('submitBtn').disabled = true;
        swapRow.classList.remove('show'); // same file — swapping won't help
        return true;
    }
    swapRow.classList.remove('show');
    document.getElementById('submitBtn').disabled = false;
    return false;
}

// ID cards are landscape credit-card ratio ~1.4–1.9 : 1
function checkAspectRatio(side, file) {
    if (!file.type.startsWith('image/')) return; // skip PDFs
    const img = new Image();
    const url = URL.createObjectURL(file);
    img.onload = function () {
        URL.revokeObjectURL(url);
        const ratio = img.naturalWidth / img.naturalHeight;
        if (ratio < 1.1) {
            // Portrait or square — ID cards are always landscape
            showError(side, 'This image appears to be portrait-oriented. ID cards should be held horizontally — please re-upload.');
            document.getElementById('dropZone' + cap(side)).classList.remove('dz-success');
            document.getElementById('submitBtn').disabled = true;
        }
    };
    img.src = url;
}

function renderPreview(side, file) {
    const wrapper = document.getElementById('preview' + cap(side) + 'Wrapper');
    const img     = document.getElementById('preview' + cap(side) + 'Img');
    const nameEl  = document.getElementById('preview' + cap(side) + 'Name');
    const zone    = document.getElementById('dropZone' + cap(side));

    nameEl.textContent = file.name;
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; img.style.display = 'block'; };
        reader.readAsDataURL(file);
    } else {
        img.style.display = 'none';
    }
    wrapper.style.display = 'block';
    zone.classList.add('dz-success');
    zone.classList.remove('dz-error');
}

function clearSide(side) {
    const input   = document.getElementById('id_document_' + side);
    const wrapper = document.getElementById('preview' + cap(side) + 'Wrapper');
    const zone    = document.getElementById('dropZone' + cap(side));
    input.value = '';
    state[side]  = null;
    wrapper.style.display = 'none';
    zone.classList.remove('dz-success', 'dz-error');
    clearError(side);
    document.getElementById('swapRow').classList.remove('show');
    document.getElementById('submitBtn').disabled = false;
}

function swapSides() {
    const frontInput = document.getElementById('id_document_front');
    const backInput  = document.getElementById('id_document_back');
    const frontFile  = frontInput.files[0];
    const backFile   = backInput.files[0];
    if (!frontFile || !backFile) return;

    // Swap via DataTransfer
    const dtFront = new DataTransfer();
    const dtBack  = new DataTransfer();
    dtFront.items.add(backFile);
    dtBack.items.add(frontFile);
    frontInput.files = dtFront.files;
    backInput.files  = dtBack.files;

    clearError('front');
    clearError('back');
    renderPreview('front', backFile);
    renderPreview('back',  frontFile);
    document.getElementById('submitBtn').disabled = false;
}

function setupDropZone(side) {
    const inputId = 'id_document_' + side;
    const zoneId  = 'dropZone' + cap(side);
    const input   = document.getElementById(inputId);
    const zone    = document.getElementById(zoneId);

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (!validateFile(file, side)) {
            this.value = '';
            return;
        }
        renderPreview(side, file);
        checkAspectRatio(side, file);
        checkDuplicate();
    });

    zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', ()  => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            const dt = new DataTransfer();
            dt.items.add(e.dataTransfer.files[0]);
            input.files = dt.files;
            input.dispatchEvent(new Event('change'));
        }
    });
}

// Prevent submit if there are visible errors
document.querySelector('form').addEventListener('submit', function (e) {
    const frontOk = document.getElementById('id_document_front').files[0];
    const backOk  = document.getElementById('id_document_back').files[0];
    if (!frontOk) { showError('front', 'Please upload the front side of your ID.'); e.preventDefault(); }
    if (!backOk)  { showError('back',  'Please upload the back side of your ID.');  e.preventDefault(); }
    if (checkDuplicate()) e.preventDefault();
});

setupDropZone('front');
setupDropZone('back');
</script>
@endpush
