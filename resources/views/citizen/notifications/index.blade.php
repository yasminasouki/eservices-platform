@extends('layouts.citizen')

@section('title', 'Notifications')

@push('styles')
<style>
    .notifs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .notifs-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1f1235;
        display: flex;
        align-items: center;
        gap: .6rem;
    }
    .notifs-title .title-icon {
        width: 40px; height: 40px;
        background: #ede9fe;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #7c3aed;
        font-size: 1.1rem;
    }
    .unread-badge {
        background: #ede9fe;
        color: #6d28d9;
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .6rem;
        border-radius: 999px;
    }

    .mark-all-btn {
        background: #ede9fe;
        border: 1px solid #ddd6fe;
        color: #6d28d9;
        font-size: .82rem;
        font-weight: 600;
        padding: .45rem 1rem;
        border-radius: 9px;
        cursor: pointer;
        transition: background .15s;
        display: flex; align-items: center; gap: .4rem;
    }
    .mark-all-btn:hover { background: #ddd6fe; }

    .notif-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(124, 58, 237, 0.06);
        border: 1px solid #f0ecff;
        overflow: hidden;
    }

    .notif-row {
        display: flex;
        align-items: flex-start;
        gap: .9rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #faf9ff;
        transition: background .12s;
        text-decoration: none;
        color: inherit;
    }
    .notif-row:last-child { border-bottom: none; }
    .notif-row:hover { background: #faf8ff; }
    .notif-row.unread { background: #f8f5ff; }
    .notif-row.unread:hover { background: #f0eaff; }

    .notif-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #a78bfa;
        flex-shrink: 0;
        margin-top: .45rem;
    }
    .notif-dot.read { background: transparent; border: 1.5px solid #e0d9ff; }

    .notif-icon-wrap {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: #ede9fe;
        display: flex; align-items: center; justify-content: center;
        color: #8b5cf6;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .notif-icon-wrap.read { background: #f3f4f6; color: #9ca3af; }

    .notif-body { flex: 1; min-width: 0; }
    .notif-msg {
        font-size: .875rem;
        color: #1f1235;
        line-height: 1.5;
        margin-bottom: .2rem;
    }
    .notif-row.read .notif-msg { color: #6b7280; font-weight: 400; }
    .notif-row.unread .notif-msg { font-weight: 600; }
    .notif-time {
        font-size: .75rem;
        color: #b4a0d4;
        font-weight: 500;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    .empty-state .empty-icon {
        width: 70px; height: 70px;
        background: #f3f0ff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #c4b5fd;
        font-size: 1.8rem;
        margin: 0 auto 1rem;
    }
    .empty-state h6 { font-weight: 700; color: #3b0764; margin-bottom: .3rem; }
    .empty-state p  { font-size: .85rem; color: #b4a0d4; margin: 0; }

    .filter-tabs {
        display: flex;
        gap: .4rem;
        margin-bottom: 1.25rem;
    }
    .filter-tab {
        padding: .35rem .85rem;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #e0d9ff;
        background: #fff;
        color: #9d7ecf;
        transition: all .15s;
    }
    .filter-tab.active, .filter-tab:hover {
        background: #ede9fe;
        border-color: #c4b5fd;
        color: #4c1d95;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Header --}}
        <div class="notifs-header">
            <div class="notifs-title">
                <div class="title-icon"><i class="bi bi-bell-fill"></i></div>
                Notifications
                @if($unreadCount > 0)
                    <span class="unread-badge">{{ $unreadCount }} unread</span>
                @endif
            </div>
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('citizen.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="mark-all-btn">
                        <i class="bi bi-check2-all"></i> Mark all as read
                    </button>
                </form>
            @endif
        </div>

        {{-- Filter tabs --}}
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filterNotifs('all', this)">All</button>
            <button class="filter-tab" onclick="filterNotifs('unread', this)">Unread</button>
            <button class="filter-tab" onclick="filterNotifs('read', this)">Read</button>
        </div>

        {{-- Notifications list --}}
        <div class="notif-card">
            @forelse($notifications as $notif)
                <a href="{{ $notif['url'] }}"
                   class="notif-row {{ $notif['read'] ? 'read' : 'unread' }}"
                   data-status="{{ $notif['read'] ? 'read' : 'unread' }}">
                    <div class="notif-dot {{ $notif['read'] ? 'read' : '' }}"></div>
                    <div class="notif-icon-wrap {{ $notif['read'] ? 'read' : '' }}">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div class="notif-body">
                        <div class="notif-msg">{{ $notif['message'] }}</div>
                        <div class="notif-time"><i class="bi bi-clock me-1"></i>{{ $notif['created_at'] }}</div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
                    <h6>You're all caught up!</h6>
                    <p>No notifications yet. We'll let you know when something happens.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterNotifs(type, btn) {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');

        document.querySelectorAll('.notif-row').forEach(row => {
            const status = row.dataset.status;
            if (type === 'all') {
                row.style.display = '';
            } else {
                row.style.display = status === type ? '' : 'none';
            }
        });
    }
</script>
@endpush
