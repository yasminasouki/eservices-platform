<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): mixed
    {
        $user          = $request->user();
        $notifications = $this->mapNotifications($user);

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount'   => $user->unreadNotifications()->count(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'count'         => $user->unreadNotifications()->count(),
            'notifications' => $this->mapNotifications($user)
                ->filter(fn ($n) => !$n['read'])->values(),
        ])->header('Cache-Control', 'no-store');
    }

    private function mapNotifications($user): \Illuminate\Support\Collection
    {
        return $user->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'message'    => $n->data['message'] ?? 'New notification',
                'url'        => $n->data['url'] ?? '#',
                'created_at' => $n->created_at->diffForHumans(),
                'read'       => !is_null($n->read_at),
            ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function markOneRead(Request $request, string $id): JsonResponse
    {
        $request->user()->notifications()->where('id', $id)->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
