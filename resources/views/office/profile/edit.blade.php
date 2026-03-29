@extends('layouts.office')

@section('title', 'Edit — '.$office->name)

@section('content')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit office profile</h2>
            <p class="text-muted mb-0">{{ $office->name }}</p>
        </div>
        <a href="{{ route('office.profile.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>All offices
        </a>
    </div>

    @if($office->municipality)
        <p class="small text-muted mb-3">
            <i class="bi bi-pin-map me-1"></i>Municipality (admin-managed): <strong>{{ $office->municipality->name }}</strong>
        </p>
    @endif

    <div class="card card-soft">
        <div class="card-body">
            <form method="POST" action="{{ route('office.profile.update', $office) }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Office name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $office->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label d-block">&nbsp;</label>
                    <span class="text-muted small">Use the official name citizens will see in the directory.</span>
                </div>

                <div class="col-12">
                    <label class="form-label">Address *</label>
                    <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                           value="{{ old('address', $office->address) }}" required>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Public email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email', $office->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                           value="{{ old('phone', $office->phone) }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                           value="{{ old('website', $office->website) }}" placeholder="https://">
                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Google Maps link</label>
                    <input type="url" name="google_maps_url" class="form-control @error('google_maps_url') is-invalid @enderror"
                           value="{{ old('google_maps_url', $office->google_maps_url) }}"
                           placeholder="https://maps.google.com/...">
                    @error('google_maps_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="latitude" class="form-control @error('latitude') is-invalid @enderror"
                           value="{{ old('latitude', $office->latitude) }}" placeholder="e.g. 33.8938">
                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="longitude" class="form-control @error('longitude') is-invalid @enderror"
                           value="{{ old('longitude', $office->longitude) }}" placeholder="e.g. 35.5018">
                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <h3 class="h6 fw-bold border-bottom pb-2 mt-2">Working hours</h3>
                    <p class="small text-muted">Leave a day marked closed or leave times empty to skip it.</p>
                </div>

                @foreach($weekdays as $day)
                    @php
                        $slot = $hoursByDay[$day] ?? null;
                        $closedName = 'wh_'.$day.'_closed';
                        $closedDefault = $slot === null ? '1' : '0';
                        $openVal = old('wh_'.$day.'_open', $slot['open'] ?? '');
                        $closeVal = old('wh_'.$day.'_close', $slot['close'] ?? '');
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <div class="fw-semibold mb-2">{{ $day }}</div>
                            <input type="hidden" name="{{ $closedName }}" value="0">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="{{ $closedName }}" value="1" id="{{ $closedName }}"
                                       @checked((string) old($closedName, $closedDefault) === '1')>
                                <label class="form-check-label small" for="{{ $closedName }}">Closed</label>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-0">Opens</label>
                                    <input type="time" name="wh_{{ $day }}_open" class="form-control form-control-sm"
                                           value="{{ $openVal }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-0">Closes</label>
                                    <input type="time" name="wh_{{ $day }}_close" class="form-control form-control-sm"
                                           value="{{ $closeVal }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="col-12">
                    <h3 class="h6 fw-bold border-bottom pb-2 mt-3">Extra contact</h3>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fax</label>
                    <input type="text" name="contact_fax" class="form-control" value="{{ old('contact_fax', $contact['fax'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hotline</label>
                    <input type="text" name="contact_hotline" class="form-control" value="{{ old('contact_hotline', $contact['hotline'] ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Internal notes (optional)</label>
                    <input type="text" name="contact_notes" class="form-control" value="{{ old('contact_notes', $contact['notes'] ?? '') }}">
                </div>

                <div class="col-12 pt-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Save profile
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
