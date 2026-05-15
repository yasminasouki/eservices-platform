@extends('layouts.admin')

@section('title', 'Notifications')

@push('styles')
<style>
    .notifs-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
    }
    .notifs-title {
        font-size: 1.25rem; font-weight: 800; color: #1e3a8a;
        display: flex; align-items: center; gap: .6rem;
    }
    .notifs-title .title-icon {
        width: 40px; height: 40px; background: #dbeafe;
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        color: #2563eb; font-size: 1.1rem;
    }
    .unread-badge {
        background: #dbeafe; color: #1e40af;
        font-size: .72rem; font-weight: 700;
        padding: .2rem .6rem; border-radius: 999px;
    }
    .mark-all-btn {
        background: #dbeafe; border: 1px solid #bfdbfe; color: #1e40af;
        font-size: .82rem; font-weight: 600; padding: .45rem 1rem;
        border-radius: 9px; cursor: pointer; transition: background .15s;
        display: flex; align-items: center; gap: .4rem;
    }
    .mark-all-btn:hover { background: #bfdbfe; }

    .filter-tabs { display: flex; gap: .4rem; margin-bottom: 1.25rem; }
    .filter-tab {
        padding: .35rem .85rem; border-radius: 999px;
        font-size: .8rem; font-weight: 600; cursor: pointer;
        border: 1px solid #bfdbfe; background: #fff; color: #60a5fa;
        transition: all .15s;
    }
    .filter-tab.active, .filter-tab:hover {
        background: #dbeafe; border-color: #93c5fd; color: #1e3a8a;
    }

    .notif-card {
        background: #fff; border-radius: 14px;
        box-shadow: 0 2px 14px rgba(37,99,235,.07);
        border: 1px solid #e0eeff; overflow: hidden;
    }
    .notif-row {
        display: flex; align-items: flex-start; gap: .9rem;
        padding: 1rem 1.25rem; border-bottom: 1px solid #f0f7ff;
        transition: background .12s; text-decoration: none; color: inherit;
    }
    .notif-row:last-child { border-bottom: none; }
    .notif-row:hover { background: #f5f9ff; }
    .notif-row.unread { background: #f0f7ff; }
    .notif-row.unread:hover { background: #e0eeff; }

    .notif-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #60a5fa; flex-shrink: 0; margin-top: .45rem;
    }
    .notif-dot.read { background: transparent; border: 1.5px solid #bfdbfe; }

    .notif-icon-wrap {
        width: 38px; height: 38px; border-radius: 10px;
        background: #dbeafe; display: flex; align-items: center; justify-content: center;
        color: #2563eb; font-size: 1rem; flex-shrink: 0;
    }
    .notif-icon-wrap.read { background: #f3f4f6; color: #9ca3af; }

    .notif-body { flex: 1; min-width: 0; }
    .notif-msg { font-size: .875rem; color: #1e3a8a; line-height: 1.5; margin-bottom: .2rem; }
    .notif-row.read .notif-msg { color: #6b7280; font-weight: 400; }
    .notif-row.unread .notif-msg { font-weight: 600; }
    .notif-time { font-size: .75rem; color: #93c5fd; font-weight: 500; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-state .empty-icon {
        width: 70px; height: 70px; background: #eff6ff; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #93c5fd; font-size: 1.8rem; margin: 0 auto 1rem;
    }
    .empty-state h6 { font-weight: 700; color: #1e3a8a; margin-bottom: .3rem; }
    .empty-state p  { font-size: .85rem; color: #93c5fd; margin: 0; }
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
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
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

        {{-- List --}}
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
                    <h6>All caught up!</h6>
                    <p>No notifications yet.</p>
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
            row.style.display = (type === 'all' || row.dataset.status === type) ? '' : 'none';
        });
    }
</script>
@endpush
