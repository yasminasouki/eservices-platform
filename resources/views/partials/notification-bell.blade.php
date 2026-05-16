@php
    $notifIndexUrl    = $notificationIndexUrl ?? route('office.notifications.index');
    $notifFeedUrl     = $notificationFeedUrl  ?? ($notifIndexUrl . '/feed');
    $notifReadAllUrl  = $notificationReadAllUrl ?? route('office.notifications.read-all');
    $notifReadBaseUrl = rtrim($notificationReadOneBaseUrl ?? url('/office/notifications'), '/');
    $notifUserId      = auth()->id();
@endphp

<style>
.notif-bell-btn {
    position: relative;
    width: 38px; height: 38px;
    background: var(--accent-pale, #ede9fe);
    border: 1px solid var(--accent-light, #ddd6fe);
    border-radius: 10px;
    color: var(--accent, #7c3aed);
    font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background .15s;
    padding: 0;
}
.notif-bell-btn:hover, .notif-bell-btn:focus {
    background: var(--accent-light, #ddd6fe);
    color: var(--accent-dark, #4c1d95);
    box-shadow: none;
    outline: none;
}
.notif-count-badge {
    position: absolute;
    top: -5px; right: -5px;
    background: #ef4444;
    color: #fff;
    font-size: .6rem;
    font-weight: 700;
    min-width: 17px; height: 17px;
    border-radius: 999px;
    display: none;
    align-items: center; justify-content: center;
    padding: 0 4px;
    border: 2px solid #fff;
    line-height: 1;
}
.notif-count-badge.visible { display: flex; }

/* Dropdown panel */
.notif-panel {
    width: 340px;
    border: 1px solid var(--accent-light, #ede9fe) !important;
    border-radius: 16px !important;
    box-shadow: 0 12px 40px rgba(0,0,0,.12) !important;
    padding: 0 !important;
    overflow: hidden;
    margin-top: 6px !important;
}

.notif-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .9rem 1.1rem .75rem;
    border-bottom: 1px solid var(--accent-pale, #f3f0ff);
}
.notif-panel-title {
    font-weight: 700;
    font-size: .92rem;
    color: #111827;
}
.notif-mark-all {
    font-size: .78rem;
    color: var(--accent, #8b5cf6);
    font-weight: 600;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    text-decoration: none;
    transition: color .12s;
}
.notif-mark-all:hover { color: var(--accent-dark, #4c1d95); }

/* List */
.notif-list {
    list-style: none;
    margin: 0; padding: 0;
    max-height: 320px;
    overflow-y: auto;
}
.notif-list::-webkit-scrollbar { width: 4px; }
.notif-list::-webkit-scrollbar-track { background: transparent; }
.notif-list::-webkit-scrollbar-thumb { background: var(--accent-light, #e0d9ff); border-radius: 4px; }

.notif-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: var(--accent-light, #c4b5fd);
}
.notif-empty i { font-size: 2rem; display: block; margin-bottom: .5rem; }
.notif-empty span { font-size: .82rem; color: #9ca3af; font-weight: 500; }

.notif-item { border-bottom: 1px solid #f9fafb; }
.notif-item:last-child { border-bottom: none; }
.notif-link {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
    padding: .8rem 1.1rem;
    text-decoration: none;
    transition: background .12s;
}
.notif-link:hover { background: var(--accent-pale, #faf8ff); }

.notif-icon-wrap {
    width: 34px; height: 34px;
    border-radius: 9px;
    background: var(--accent-pale, #ede9fe);
    display: flex; align-items: center; justify-content: center;
    color: var(--accent, #8b5cf6);
    font-size: .95rem;
    flex-shrink: 0;
    margin-top: .1rem;
}
.notif-body { flex: 1; min-width: 0; }
.notif-msg {
    font-size: .83rem;
    color: #111827;
    line-height: 1.45;
    margin-bottom: .2rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.notif-time {
    font-size: .72rem;
    color: #9ca3af;
    font-weight: 500;
}

/* Footer */
.notif-footer {
    border-top: 1px solid var(--accent-pale, #f3f0ff);
    padding: .6rem 1rem;
    text-align: center;
}
.notif-footer a {
    font-size: .8rem;
    color: var(--accent, #8b5cf6);
    font-weight: 600;
    text-decoration: none;
}
.notif-footer a:hover { color: var(--accent-dark, #4c1d95); }
</style>

<div class="dropdown" id="notif-dropdown">
    <button class="notif-bell-btn" type="button"
            id="notifBellBtn"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
            aria-expanded="false"
            aria-label="Notifications">
        <i class="bi bi-bell-fill"></i>
        <span class="notif-count-badge" id="notif-badge"></span>
    </button>

    <div class="dropdown-menu dropdown-menu-end notif-panel" aria-labelledby="notifBellBtn">
        <div class="notif-panel-header">
            <span class="notif-panel-title">Notifications</span>
            <button class="notif-mark-all" id="notif-mark-all" type="button">Mark all as read</button>
        </div>

        <ul class="notif-list" id="notif-list">
            <li id="notif-empty" class="notif-empty">
                <i class="bi bi-bell-slash"></i>
                <span>You're all caught up!</span>
            </li>
        </ul>

        <div class="notif-footer">
            <a href="{{ $notifIndexUrl }}">View all notifications</a>
        </div>
    </div>
</div>

<script>
(function () {
    const CSRF          = @json(csrf_token());
    const INDEX_URL     = @json($notifIndexUrl);
    const FEED_URL      = @json($notifFeedUrl);
    const READ_ALL      = @json($notifReadAllUrl);
    const READ_BASE_URL = @json($notifReadBaseUrl);
    const badge         = document.getElementById('notif-badge');
    const list          = document.getElementById('notif-list');
    const empty         = document.getElementById('notif-empty');
    const markAllBtn    = document.getElementById('notif-mark-all');

    function renderNotifications(data) {
        if (data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.classList.add('visible');
        } else {
            badge.classList.remove('visible');
        }

        list.querySelectorAll('.notif-item').forEach(el => el.remove());

        if (!data.notifications || data.notifications.length === 0) {
            empty.style.display = '';
            return;
        }

        empty.style.display = 'none';

        data.notifications.forEach(function (n) {
            const li = document.createElement('li');
            li.className = 'notif-item';
            li.dataset.id = n.id;
            li.innerHTML =
                '<a href="' + escAttr(n.url) + '" class="notif-link">' +
                    '<div class="notif-icon-wrap"><i class="bi bi-envelope-fill"></i></div>' +
                    '<div class="notif-body">' +
                        '<div class="notif-msg">' + escHtml(n.message) + '</div>' +
                        '<div class="notif-time">' + escHtml(n.created_at) + '</div>' +
                    '</div>' +
                '</a>';

            li.querySelector('.notif-link').addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href') || '#';
                markOne(n.id).finally(function () { window.location.href = href; });
            });

            list.insertBefore(li, empty);
        });
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
    function escAttr(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function markOne(id) {
        return fetch(READ_BASE_URL + '/' + encodeURIComponent(id) + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin', keepalive: true,
        }).catch(function () {});
    }

    markAllBtn.addEventListener('click', function () {
        fetch(READ_ALL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).then(function () { poll(); }).catch(function () {});
    });

    // Clear badge when dropdown is opened
    document.getElementById('notif-dropdown').addEventListener('show.bs.dropdown', function () {
        badge.classList.remove('visible');
        fetch(READ_ALL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).catch(function () {});
    });

    function poll() {
        fetch(FEED_URL, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) { if (data) renderNotifications(data); })
        .catch(function () {});
    }

    poll();
    setInterval(poll, 5000);

    var userId = @json($notifUserId);
    function subscribeEcho(echo) {
        echo.private('App.Models.User.' + userId)
            .notification(function () { poll(); });
    }
    if (window.Echo) {
        subscribeEcho(window.Echo);
    } else {
        var echoCheck = setInterval(function () {
            if (window.Echo) { clearInterval(echoCheck); subscribeEcho(window.Echo); }
        }, 300);
    }
})();
</script>
