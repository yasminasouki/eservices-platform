@extends('layouts.office')

@section('title', $isEdit ? 'Edit service' : 'New service')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $isEdit ? 'Edit service' : 'New service' }}</h2>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.services.index', $office) }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ $formAction }}" class="row g-3">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $service->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category *</label>
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
                    <label class="form-label">Price *</label>
                    <input type="number" name="price" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price', $service->price) }}" required>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Duration amount</label>
                    <input type="number" name="duration" min="1" class="form-control @error('duration') is-invalid @enderror"
                           value="{{ old('duration', $service->duration) }}" placeholder="Optional">
                    @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Duration unit *</label>
                    <select name="duration_unit" class="form-select @error('duration_unit') is-invalid @enderror" required>
                        @foreach(['minutes' => 'Minutes', 'hours' => 'Hours', 'days' => 'Days'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('duration_unit', $service->duration_unit) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('duration_unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Required documents</label>
                    <textarea name="required_documents" rows="4" class="form-control @error('required_documents') is-invalid @enderror"
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
                    <button class="btn btn-success">
                        <i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
