@extends('layouts.office')

@section('title', __('ui.office_notif_title'))

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    .notifs-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem;
    }
    .notifs-title-wrap { display: flex; align-items: center; gap: .65rem; }
    .title-icon {
        width: 40px; height: 40px; background: #dcfce7; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #16a34a; font-size: 1.1rem;
    }
    .unread-badge {
        background: #dcfce7; color: #14532d;
        font-size: .72rem; font-weight: 700;
        padding: .2rem .6rem; border-radius: 999px;
    }
    .mark-all-btn {
        background: #dcfce7; border: 1px solid #bbf7d0;
        color: #14532d; font-size: .82rem; font-weight: 600;
        padding: .45rem 1rem; border-radius: 9px;
        cursor: pointer; transition: background .15s;
        display: flex; align-items: center; gap: .4rem;
    }
    .mark-all-btn:hover { background: #bbf7d0; }

    /* Filter tabs */
    .filter-tabs { display: flex; gap: .4rem; margin-bottom: 1.25rem; }
    .filter-tab {
        padding: .35rem .85rem; border-radius: 999px;
        font-size: .8rem; font-weight: 600; cursor: pointer;
        border: 1px solid #d1fae5; background: #fff; color: #52916b;
        transition: all .15s;
    }
    .filter-tab.active, .filter-tab:hover {
        background: #dcfce7; border-color: #86efac; color: #14532d;
    }

    /* Card */
    .notif-card {
        background: #fff; border-radius: 16px;
        box-shadow: 0 2px 14px rgba(22,163,74,.06);
        border: 1px solid #d1fae5; overflow: hidden;
    }
    .notif-row {
        display: flex; align-items: flex-start; gap: .9rem;
        padding: 1rem 1.25rem; border-bottom: 1px solid #f0fdf4;
        transition: background .12s; text-decoration: none; color: inherit;
    }
    .notif-row:last-child { border-bottom: none; }
    .notif-row:hover { background: #f0fdf4; }
    .notif-row.unread { background: #f0fdf4; }
    .notif-row.unread:hover { background: #dcfce7; }

    .notif-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #16a34a; flex-shrink: 0; margin-top: .5rem;
    }
    .notif-dot.read { background: transparent; border: 1.5px solid #d1fae5; }

    .notif-icon-wrap {
        width: 38px; height: 38px; border-radius: 10px;
        background: #dcfce7; display: flex; align-items: center; justify-content: center;
        color: #16a34a; font-size: 1rem; flex-shrink: 0;
    }
    .notif-icon-wrap.read { background: #f3f4f6; color: #9ca3af; }

    .notif-body { flex: 1; min-width: 0; }
    .notif-msg { font-size: .875rem; color: #0f2d13; line-height: 1.5; margin-bottom: .2rem; }
    .notif-row.read .notif-msg  { color: #6b7280; font-weight: 400; }
    .notif-row.unread .notif-msg { font-weight: 600; }
    .notif-time { font-size: .75rem; color: #86efac; font-weight: 500; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-icon {
        width: 70px; height: 70px; background: #f0fdf4; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        color: #86efac; font-size: 1.8rem; margin: 0 auto 1rem;
    }
    .empty-state h6 { font-weight: 700; color: #0f2d13; margin-bottom: .3rem; }
    .empty-state p  { font-size: .85rem; color: #52916b; margin: 0; }
</style>
@endpush

@section('content')

    <div class="notifs-header">
        <div class="notifs-title-wrap">
            <div class="title-icon"><i class="bi bi-bell-fill"></i></div>
            <div>
                <div class="page-title">{{ __('ui.office_notif_title') }}
                    @if($unreadCount > 0)
                        <span class="unread-badge ms-1">{{ $unreadCount }} {{ __('ui.office_notif_unread') }}</span>
                    @endif
                </div>
                <p class="page-sub">{{ __('ui.office_notif_subtitle') }}</p>
            </div>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('office.notifications.read-all') }}">
                @csrf
                <button type="submit" class="mark-all-btn">
                    <i class="bi bi-check2-all"></i> {{ __('ui.office_notif_mark_all') }}
                </button>
            </form>
        @endif
    </div>

    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterNotifs('all', this)">{{ __('ui.office_notif_tab_all') }}</button>
        <button class="filter-tab" onclick="filterNotifs('unread', this)">{{ __('ui.office_notif_tab_unread') }}</button>
        <button class="filter-tab" onclick="filterNotifs('read', this)">{{ __('ui.office_notif_tab_read') }}</button>
    </div>

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
                <h6>{{ __('ui.office_notif_empty_title') }}</h6>
                <p>{{ __('ui.office_notif_empty_desc') }}</p>
            </div>
        @endforelse
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
