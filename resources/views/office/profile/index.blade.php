@extends('layouts.office')

@section('title', __('ui.office_profile_title'))

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    /* Office card */
    .office-card {
        background: #fff; border-radius: 14px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 12px rgba(21,128,61,.06);
        overflow: hidden; height: 100%;
    }
    .office-card-header {
        background: #f0fdf4; padding: 1rem 1.25rem;
        border-bottom: 1px solid #d1fae5;
        display: flex; align-items: center; gap: .6rem;
    }
    .office-icon {
        width: 40px; height: 40px; background: #dcfce7; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: #16a34a; font-size: 1.1rem; flex-shrink: 0;
    }
    .office-name { font-weight: 700; font-size: 1rem; color: #0f2d13; margin: 0; }
    .office-card-body {
        padding: 1.1rem 1.25rem; display: flex; flex-direction: column; gap: .5rem; flex-grow: 1;
    }
    .office-address {
        font-size: .82rem; color: #52916b; flex-grow: 1;
        display: flex; align-items: flex-start; gap: .35rem;
    }

    .btn-edit-profile {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .84rem; padding: .45rem 1.1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
        align-self: flex-start; margin-top: .4rem;
    }
    .btn-edit-profile:hover { background: #15803d; color: #fff; }

    /* Alert */
    .alert-no-office {
        background: #fef3c7; border: 1px solid #fde68a; color: #92400e;
        border-radius: 12px; padding: 1rem 1.25rem; font-size: .875rem;
        display: flex; align-items: center; gap: .6rem;
    }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="mb-4">
        <div class="page-title">{{ __('ui.office_profile_title') }}</div>
        <p class="page-sub">{{ __('ui.office_profile_subtitle') }}</p>
    </div>

    @if($offices->isEmpty())
        <div class="alert-no-office">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:1.1rem;flex-shrink:0;"></i>
            {{ __('ui.office_profile_no_office') }}
        </div>
    @else
        <div class="row g-3">
            @foreach($offices as $o)
                <div class="col-md-6 col-lg-4">
                    <div class="office-card d-flex flex-column">
                        <div class="office-card-header">
                            <div class="office-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <h3 class="office-name">{{ $o->name }}</h3>
                        </div>
                        <div class="office-card-body">
                            <div class="office-address">
                                <i class="bi bi-geo-alt" style="flex-shrink:0;margin-top:.15rem;"></i>
                                {{ \Illuminate\Support\Str::limit($o->address, 90) }}
                            </div>
                            <a href="{{ route('office.profile.edit', $o) }}" class="btn-edit-profile">
                                <i class="bi bi-pencil"></i>{{ __('ui.office_profile_edit') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endsection
