<?php

namespace App\Http\Controllers\Citizen;

use App\Broadcasting\OfficeChatBroadcaster;
use App\Events\OfficeChatMessageSent;
use App\Http\Controllers\Controller;
use App\Models\GovernmentOffice;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewChatMessageNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CitizenOfficeChatController extends Controller
{
    public function index(GovernmentOffice $office): View
    {
        $citizen = auth()->user();
        abort_unless($citizen->isCitizen(), 403);

        $this->markThreadReadForUser($office, $citizen);

        $messages = Message::query()
            ->forOfficeCitizenThread($office->id, $citizen->id)
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('citizen.offices.chat', [
            'office' => $office,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, GovernmentOffice $office): RedirectResponse|JsonResponse
    {
        $citizen = auth()->user();
        abort_unless($citizen->isCitizen(), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $staffRecipient = $office->staff()
            ->where('role', 'office_user')
            ->orderBy('users.name')
            ->first();

        if (! $staffRecipient) {
            return back()->withErrors([
                'body' => 'This office has no staff assigned yet. Messages cannot be sent until staff are available.',
            ]);
        }

        $message = Message::create([
            'sender_id' => $citizen->id,
            'receiver_id' => $staffRecipient->id,
            'service_request_id' => null,
            'government_office_id' => $office->id,
            'body' => $validated['body'],
        ]);
        $message->load('sender:id,name');
        $broadcastEvent = new OfficeChatMessageSent($message);
        OfficeChatBroadcaster::broadcast($broadcastEvent);

        $chatUrl = route('office.chat.show', [$office, $citizen]);
        $office->staff()
            ->where('role', 'office_user')
            ->get()
            ->each(function (User $user) use ($citizen, $validated, $chatUrl) {
                $user->notify(new NewChatMessageNotification(
                    $citizen->name,
                    $validated['body'],
                    $chatUrl,
                ));
            });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $broadcastEvent->broadcastWith(),
            ]);
        }

        return redirect()
            ->route('citizen.offices.chat', $office)
            ->with('success', 'Message sent.');
    }

    private function markThreadReadForUser(GovernmentOffice $office, User $user): void
    {
        Message::query()
            ->forOfficeCitizenThread($office->id, $user->id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
