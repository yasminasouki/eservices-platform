@extends('layouts.office')

@section('title', $isEdit ? __('ui.office_cat_form_edit_title') : __('ui.office_cat_form_new_title'))

@push('styles')
<style>
    .page-title { font-size: 1.25rem; font-weight: 800; color: #0f2d13; margin-bottom: .15rem; }
    .page-sub   { font-size: .83rem; color: #52916b; margin: 0; }

    .btn-back {
        display: inline-flex; align-items: center; gap: .35rem;
        background: #f0fdf4; border: 1px solid #d1fae5; color: #15803d;
        font-weight: 600; font-size: .84rem; padding: .44rem 1rem;
        border-radius: 9px; text-decoration: none; transition: background .15s;
    }
    .btn-back:hover { background: #dcfce7; }

    /* Form card */
    .form-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #d1fae5;
        box-shadow: 0 2px 16px rgba(21,128,61,.06);
        padding: 1.5rem;
    }

    .form-label { font-size: .82rem; font-weight: 600; color: #14532d; }
    .form-control { border-color: #d1fae5; font-size: .875rem; }
    .form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }

    .btn-submit {
        display: inline-flex; align-items: center; gap: .4rem;
        background: #16a34a; border: none; color: #fff;
        font-weight: 700; font-size: .9rem; padding: .55rem 1.4rem;
        border-radius: 9px; transition: background .15s; cursor: pointer;
    }
    .btn-submit:hover { background: #15803d; }
</style>
@endpush

@section('content')

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="page-title">{{ $isEdit ? __('ui.office_cat_form_edit_title') : __('ui.office_cat_form_new_title') }}</div>
            <p class="page-sub">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.categories.index', $office) }}" class="btn-back">
            <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>{{ __('ui.office_cat_form_back') }}
        </a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ $formAction }}" class="row g-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="col-12">
                <label class="form-label">{{ __('ui.office_cat_form_name') }} <span class="text-danger">*</span></label>
                <input type="text" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $category->name) }}" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">{{ __('ui.office_cat_form_description') }}</label>
                <textarea name="description" rows="3"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check2"></i>{{ $isEdit ? __('ui.office_cat_form_update_btn') : __('ui.office_cat_form_create_btn') }}
                </button>
            </div>
        </form>
    </div>

@endsection
