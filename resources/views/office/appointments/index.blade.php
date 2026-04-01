@extends('layouts.office')

@section('title', 'Time Slots — ' . $office->name)

@section('content')
<div class="container py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="bi bi-calendar-plus me-2" style="color:#2e7d32"></i>Time Slots
            </h4>
            <p class="text-muted small mb-0">{{ $office->name }}</p>
        </div>
    </div>

    {{-- Add Slot Form --}}
    <div class="card card-soft mb-4">
        <div class="card-body p-4">
            <h6 class="fw-semibold mb-3">Add New Time Slot</h6>
            <form method="POST" action="{{ route('office.slots.store', $office) }}" class="row g-3 align-items-end">
                @csrf

                <div class="col-sm-4">
                    <label class="form-label small fw-semibold text-muted">Date</label>
                    <input type="date" name="date"
                           class="form-control @error('date') is-invalid @enderror"
                           value="{{ old('date') }}"
                           min="{{ date('Y-m-d') }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-sm-3">
                    <label class="form-label small fw-semibold text-muted">Start Time</label>
                    <input type="time" name="start_time"
                           class="form-control @error('start_time') is-invalid @enderror"
                           value="{{ old('start_time') }}" required>
                    @error('start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-sm-3">
                    <label class="form-label small fw-semibold text-muted">End Time</label>
                    <input type="time" name="end_time"
                           class="form-control @error('end_time') is-invalid @enderror"
                           value="{{ old('end_time') }}" required>
                    @error('end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-sm-2">
                    <button type="submit" class="btn w-100 text-white" style="background:#2e7d32;border-color:#2e7d32">
                        <i class="bi bi-plus-lg me-1"></i>Add Slot
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Slots Table --}}
    <div class="card card-soft">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $slot)
                            <tr>
                                <td class="ps-4">{{ \Carbon\Carbon::parse($slot->date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                <td>
                                    @if($slot->is_available)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Booked</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($slot->is_available)
                                        <form method="POST"
                                              action="{{ route('office.slots.destroy', [$office, $slot]) }}"
                                              onsubmit="return confirm('Delete this time slot?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-calendar-x me-2"></i>No time slots added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($slots->hasPages())
        <div class="mt-3 d-flex justify-content-center">
            {{ $slots->links() }}
        </div>
    @endif

</div>
@endsection
