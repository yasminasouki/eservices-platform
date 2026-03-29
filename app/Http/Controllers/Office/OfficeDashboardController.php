<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class OfficeDashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $offices = $user->governmentOffices()->orderBy('name')->get(['id', 'name']);
        $officeIds = $offices->pluck('id');

        if ($officeIds->isEmpty()) {
            return view('dashboards.office-unassigned');
        }

        $this->syncOfficeContextSession($offices);

        $pending = ServiceRequest::query()
            ->whereIn('government_office_id', $officeIds)
            ->where('status', 'pending')
            ->count();

        $completedToday = ServiceRequest::query()
            ->whereIn('government_office_id', $officeIds)
            ->where('status', 'completed')
            ->whereDate('completed_at', now()->toDateString())
            ->count();

        $appointmentsToday = Appointment::query()
            ->whereIn('government_office_id', $officeIds)
            ->whereHas('timeSlot', function ($q) {
                $q->whereDate('date', now()->toDateString());
            })
            ->count();

        $avgRating = Feedback::query()
            ->whereIn('government_office_id', $officeIds)
            ->whereNotNull('rating')
            ->avg('rating');

        $latestRequests = ServiceRequest::query()
            ->whereIn('government_office_id', $officeIds)
            ->with(['citizen:id,name', 'service:id,name'])
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('dashboards.office', [
            'offices'             => $offices,
            'pending'             => $pending,
            'completedToday'      => $completedToday,
            'appointmentsToday'   => $appointmentsToday,
            'averageRating'       => $avgRating !== null ? round((float) $avgRating, 1) : null,
            'latestRequests'      => $latestRequests,
        ]);
    }

    /** @param  \Illuminate\Support\Collection<int, \App\Models\GovernmentOffice>  $offices */
    private function syncOfficeContextSession($offices): void
    {
        $ids = $offices->pluck('id')->all();
        $sid = session('office_context_id');

        if (! $sid || ! in_array((int) $sid, $ids, true)) {
            session(['office_context_id' => $offices->first()->id]);
        }
    }
}
