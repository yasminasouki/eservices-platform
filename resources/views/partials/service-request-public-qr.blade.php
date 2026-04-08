{{--
    Public offline tracking: QR encodes $trackingUrl (no login required).
    @var string $trackingUrl
    @var string $trackingQrDataUri  data:image/svg+xml;base64,...
    @var string $referenceCode  e.g. SR-...
--}}
<div class="card card-soft mb-4 border-primary border-opacity-25" id="public-tracking-qr">
    <div class="card-header bg-white border-0 fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-qr-code-scan text-primary"></i>
        <span>Offline status (QR)</span>
    </div>
    <div class="card-body">
        <div class="row align-items-center g-4">
            <div class="col-auto mx-auto mx-md-0">
                <img src="{{ $trackingQrDataUri }}"
                     width="260"
                     height="260"
                     alt="QR code linking to public request status"
                     class="rounded border bg-white p-2 shadow-sm">
            </div>
            <div class="col">
                <p class="small text-muted mb-2">
                    Anyone can scan this code (phone camera or QR app) to open a <strong>public status page</strong> without signing in.
                    No personal data is shown there—only service, office, and current status.
                </p>
                <p class="small mb-2">
                    <span class="text-muted">Reference</span>
                    <span class="font-monospace fw-semibold ms-1">{{ $referenceCode }}</span>
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-copy-track-url" data-url="{{ $trackingUrl }}">
                        <i class="bi bi-link-45deg me-1"></i>Copy link
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Print
                    </button>
                    <a href="{{ $trackingUrl }}" class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Open status page
                    </a>
                </div>
                <p class="small text-muted mb-0 mt-2 font-monospace text-break">{{ $trackingUrl }}</p>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script>
        (function () {
            var btn = document.getElementById('btn-copy-track-url');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var u = btn.getAttribute('data-url');
                if (!u) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(u).then(function () {
                        btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied';
                        setTimeout(function () {
                            btn.innerHTML = '<i class="bi bi-link-45deg me-1"></i>Copy link';
                        }, 2000);
                    });
                }
            });
        })();
    </script>
@endpush
