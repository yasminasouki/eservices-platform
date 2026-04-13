@extends('layouts.admin')

@section('title', $isEdit ? 'Edit municipality' : 'Create municipality')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $isEdit ? 'Edit municipality' : 'Create municipality' }}</h2>
            <p class="text-muted mb-0">Name and optional region. Assign offices from the Government offices screen.</p>
        </div>
        <a href="{{ route('admin.municipalities.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ $formAction }}" class="row g-3">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $municipality->name) }}" required maxlength="255">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Region</label>
                    <input type="text" name="region" class="form-control" value="{{ old('region', $municipality->region) }}" maxlength="255" placeholder="e.g. Mount Lebanon">
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>{{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
