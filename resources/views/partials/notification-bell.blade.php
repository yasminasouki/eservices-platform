{{-- Notification Bell — polled every 5 s via JS fetch --}}
<div class="dropdown" id="notif-dropdown">
    <button
        class="btn btn-outline-light btn-sm position-relative"
        type="button"
        id="notifBellBtn"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        aria-label="Notifications"
    >
        <i class="bi bi-bell-fill"></i>
        <span
            id="notif-badge"
            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
            style="display:none; font-size:.65rem;"
        ></span>
    </button>

    <div class="dropdown-menu dropdown-menu-end shadow p-0" style="min-width:22rem; max-width:26rem;" aria-labelledby="notifBellBtn">
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom bg-light rounded-top">
            <span class="fw-semibold small">Notifications</span>
            <button
                id="notif-mark-all"
                class="btn btn-link btn-sm p-0 text-decoration-none text-primary small"
                type="button"
            >Mark all as read</button>
        </div>

        <ul id="notif-list" class="list-unstyled mb-0" style="max-height:320px; overflow-y:auto;">
            <li class="text-center text-muted small py-4" id="notif-empty">No new notifications</li>
        </ul>
    </div>
</div>

<script>
(function () {
    const CSRF      = '{{ csrf_token() }}';
    const INDEX_URL = '{{ route('office.notifications.index') }}';
    const READ_ALL  = '{{ route('office.notifications.read-all') }}';
    const badge     = document.getElementById('notif-badge');
    const list      = document.getElementById('notif-list');
    const empty     = document.getElementById('notif-empty');
    const markAllBtn = document.getElementById('notif-mark-all');

    function renderNotifications(data) {
        if (data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }

        // Remove existing items, keep the empty placeholder
        list.querySelectorAll('.notif-item').forEach(el => el.remove());

        if (!data.notifications || data.notifications.length === 0) {
            empty.style.display = '';
            return;
        }

        empty.style.display = 'none';

        data.notifications.forEach(function (n) {
            const li = document.createElement('li');
            li.className = 'notif-item border-bottom';
            li.dataset.id = n.id;
            li.innerHTML =
                '<a href="' + n.url + '" class="d-flex gap-2 px-3 py-2 text-decoration-none text-dark notif-link">' +
                    '<i class="bi bi-envelope-fill text-primary mt-1 flex-shrink-0"></i>' +
                    '<div class="flex-grow-1 overflow-hidden">' +
                        '<div class="small lh-sm">' + escHtml(n.message) + '</div>' +
                        '<div class="text-muted" style="font-size:.72rem;">' + escHtml(n.created_at) + '</div>' +
                    '</div>' +
                '</a>';

            li.querySelector('.notif-link').addEventListener('click', function () {
                markOne(n.id);
            });

            list.insertBefore(li, empty);
        });
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function markOne(id) {
        fetch('{{ url('office/notifications') }}/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).catch(() => {});
    }

    function poll() {
        fetch(INDEX_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => { if (data) renderNotifications(data); })
        .catch(() => {});
    }

    markAllBtn.addEventListener('click', function () {
        fetch(READ_ALL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        })
        .then(() => poll())
        .catch(() => {});
    });

    // Initial load + poll every 5 seconds
    poll();
    setInterval(poll, 5000);
})();
</script>
