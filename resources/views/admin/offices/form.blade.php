@extends('layouts.admin')

@section('title', $isEdit ? 'Edit Office' : 'Create Office')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $isEdit ? 'Edit Government Office' : 'Create Government Office' }}</h2>
            <p class="text-muted mb-0">Define office details and municipality assignment.</p>
        </div>
        <a href="{{ route('admin.offices.index') }}" class="btn btn-outline-secondary">
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
                    <label class="form-label">Office Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $office->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Municipality</label>
                    <select name="municipality_id" class="form-select">
                        <option value="">-- Unassigned --</option>
                        @foreach($municipalities as $municipality)
                            <option value="{{ $municipality->id }}"
                                @selected((string) old('municipality_id', $office->municipality_id) === (string) $municipality->id)>
                                {{ $municipality->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Address *</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $office->address) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $office->email) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $office->phone) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="{{ old('website', $office->website) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Google Maps URL</label>
                    <input type="url" name="google_maps_url" class="form-control" value="{{ old('google_maps_url', $office->google_maps_url) }}">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active"
                               @checked(old('is_active', $isEdit ? $office->is_active : true))>
                        <label class="form-check-label" for="is_active">
                            Office is active
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>{{ $isEdit ? 'Update Office' : 'Create Office' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
