<?php

namespace App\Http\Controllers\Office;

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
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OfficeOfficeChatController extends Controller
{
    public function index(GovernmentOffice $office): View
    {
        $citizens = $this->conversationCitizens($office);

        $threads = $citizens->map(function (User $citizen) use ($office) {
            $last = Message::query()
                ->forOfficeCitizenThread($office->id, $citizen->id)
                ->latest()
                ->first();

            return [
                'citizen' => $citizen,
                'last_message' => $last,
                'unread_for_office' => Message::query()
                    ->forOfficeCitizenThread($office->id, $citizen->id)
                    ->where('receiver_id', auth()->id())
                    ->where('is_read', false)
                    ->count(),
            ];
        })->sortByDesc(fn ($t) => $t['last_message']?->created_at?->timestamp ?? 0)->values();

        return view('office.chat.index', [
            'office' => $office,
            'threads' => $threads,
        ]);
    }

    public function show(GovernmentOffice $office, User $citizen): View
    {
        abort_unless($citizen->isCitizen(), 404);

        $this->markThreadReadForUser($office, auth()->user(), $citizen);

        $messages = Message::query()
            ->forOfficeCitizenThread($office->id, $citizen->id)
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('office.chat.show', [
            'office' => $office,
            'citizen' => $citizen,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, GovernmentOffice $office, User $citizen): RedirectResponse|JsonResponse
    {
        abort_unless($citizen->isCitizen(), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $citizen->id,
            'service_request_id' => null,
            'government_office_id' => $office->id,
            'body' => $validated['body'],
        ]);
        $message->load('sender:id,name');
        $broadcastEvent = new OfficeChatMessageSent($message);
        OfficeChatBroadcaster::broadcast($broadcastEvent);

        $citizen->notify(new NewChatMessageNotification(
            auth()->user()->name,
            $validated['body'],
            route('citizen.offices.chat', $office),
        ));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $broadcastEvent->broadcastWith(),
            ]);
        }

        return redirect()
            ->route('office.chat.show', [$office, $citizen])
            ->with('success', 'Message sent.');
    }

    /**
     * @return Collection<int, User>
     */
    private function conversationCitizens(GovernmentOffice $office): Collection
    {
        $ids = Message::query()
            ->where('government_office_id', $office->id)
            ->whereNull('service_request_id')
            ->get()
            ->flatMap(fn (Message $m) => [$m->sender_id, $m->receiver_id])
            ->unique()
            ->values();

        return User::query()
            ->whereIn('id', $ids)
            ->where('role', 'citizen')
            ->orderBy('name')
            ->get();
    }

    private function markThreadReadForUser(GovernmentOffice $office, User $reader, User $citizen): void
    {
        Message::query()
            ->forOfficeCitizenThread($office->id, $citizen->id)
            ->where('receiver_id', $reader->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
