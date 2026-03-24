<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'offices'            => GovernmentOffice::count(),
            'municipality_users' => User::where('role', 'office_user')->count(),
            'citizens'           => User::where('role', 'citizen')->count(),
            'requests'           => ServiceRequest::count(),
            'revenue'            => (float) Payment::where('status', 'completed')->sum('amount'),
        ];

        $latestRequests = ServiceRequest::query()
            ->with(['citizen:id,name', 'governmentOffice:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        $volumeLeaderboard = ServiceRequest::query()
            ->select([
                'government_offices.name',
                DB::raw('COUNT(service_requests.id) as request_count'),
            ])
            ->join('government_offices', 'government_offices.id', '=', 'service_requests.government_office_id')
            ->groupBy('government_offices.id', 'government_offices.name')
            ->orderByDesc('request_count')
            ->limit(10)
            ->get();

        $revenueLeaderboard = Payment::query()
            ->select([
                'government_offices.name',
                DB::raw('SUM(payments.amount) as revenue'),
            ])
            ->join('service_requests', 'service_requests.id', '=', 'payments.service_request_id')
            ->join('government_offices', 'government_offices.id', '=', 'service_requests.government_office_id')
            ->where('payments.status', 'completed')
            ->groupBy('government_offices.id', 'government_offices.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'stats'              => $stats,
            'latestRequests'     => $latestRequests,
            'volumeLeaderboard'  => $volumeLeaderboard,
            'revenueLeaderboard' => $revenueLeaderboard,
            'volumeLabels'       => $volumeLeaderboard->pluck('name')->values()->all(),
            'volumeCounts'       => $volumeLeaderboard->pluck('request_count')->map(fn ($v) => (int) $v)->values()->all(),
            'revenueLabels'      => $revenueLeaderboard->pluck('name')->values()->all(),
            'revenueAmounts'     => $revenueLeaderboard->pluck('revenue')->map(fn ($v) => (float) $v)->values()->all(),
        ]);
    }
}
