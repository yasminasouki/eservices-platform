@extends('layouts.office')

@section('title', $isEdit ? 'Edit category' : 'New category')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $isEdit ? 'Edit category' : 'New category' }}</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.categories.index', $office) }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ $formAction }}" class="row g-3">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $category->name) }}" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <button class="btn btn-success">
                        <i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
