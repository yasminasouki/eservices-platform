<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\View\View;

class CitizenDashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $activeRequests = ServiceRequest::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', ['completed', 'rejected'])
            ->count();

        $completed = ServiceRequest::query()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $totalPaid = (float) Payment::query()
            ->whereHas('serviceRequest', fn ($q) => $q->where('user_id', $userId))
            ->where('status', 'completed')
            ->sum('amount');

        $upcomingAppointments = 0;

        $recentRequests = ServiceRequest::query()
            ->where('user_id', $userId)
            ->with(['service:id,name', 'governmentOffice:id,name'])
            ->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboards.citizen', [
            'activeRequests' => $activeRequests,
            'completedRequests' => $completed,
            'upcomingAppointments' => $upcomingAppointments,
            'totalPaid' => $totalPaid,
            'recentRequests' => $recentRequests,
        ]);
    }
}
