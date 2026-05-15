@extends('layouts.office')

@section('title', $isEdit ? 'Edit Service' : 'New Service')

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
    .form-control,
    .form-select { border-color: #d1fae5; font-size: .875rem; }
    .form-control:focus,
    .form-select:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
    .form-text { font-size: .74rem; color: #52916b; }
    .form-check-label { font-size: .85rem; color: #374151; }

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
            <div class="page-title">{{ $isEdit ? 'Edit Service' : 'New Service' }}</div>
            <p class="page-sub">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.services.index', $office) }}" class="btn-back">
            <i class="bi bi-arrow-left"></i>Back to services
        </a>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ $formAction }}" class="row g-3">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="col-md-6">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $service->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Category <span class="text-danger">*</span></label>
                <select name="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror" required>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected((string) old('service_category_id', $service->service_category_id) === (string) $c->id)>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                @error('service_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Price <span class="text-danger">*</span></label>
                <input type="number" name="price" step="0.01" min="0"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $service->price) }}" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Duration amount</label>
                <input type="number" name="duration" min="1"
                       class="form-control @error('duration') is-invalid @enderror"
                       value="{{ old('duration', $service->duration) }}" placeholder="Optional">
                @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label">Duration unit <span class="text-danger">*</span></label>
                <select name="duration_unit" class="form-select @error('duration_unit') is-invalid @enderror" required>
                    @foreach(['minutes' => 'Minutes', 'hours' => 'Hours', 'days' => 'Days'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('duration_unit', $service->duration_unit) === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('duration_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Required documents</label>
                <textarea name="required_documents" rows="4"
                          class="form-control @error('required_documents') is-invalid @enderror"
                          placeholder="One document name per line">{{ old('required_documents', $service->required_documents ? implode("\n", $service->required_documents) : '') }}</textarea>
                <div class="form-text">Citizens will be asked to upload these when the citizen portal is available.</div>
                @error('required_documents')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                           @checked((string) old('is_active', $service->is_active ? '1' : '0') === '1')>
                    <label class="form-check-label" for="is_active">Service is visible / active</label>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn-submit">
                    <i class="bi bi-check2"></i>{{ $isEdit ? 'Update service' : 'Create service' }}
                </button>
            </div>
        </form>
    </div>

@endsection
