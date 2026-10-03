<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $messages = $request->user()->receivedMessages()
            ->with('author')
            ->latest()
            ->get();

        return Inertia::render('employee/messages/index', [
            'messages' => MessageResource::collection($messages),
        ]);
    }

    public function show(Request $request, Message $message): Response
    {
        $this->authorize('view', $message);

        $received = $request->user()->receivedMessages()->with(['author', 'attachments'])->findOrFail($message->id);

        if ($received->pivot->read_at === null) {
            $message->recipients()->updateExistingPivot($request->user()->id, ['read_at' => now()]);
            $received->pivot->read_at = now();
        }

        return Inertia::render('employee/messages/show', [
            'message' => new MessageResource($received),
        ]);
    }

    public function acknowledge(Request $request, Message $message): RedirectResponse
    {
        $this->authorize('acknowledge', $message);

        $message->recipients()->updateExistingPivot($request->user()->id, [
            'acknowledged_at' => now(),
            'read_at' => now(),
        ]);

        return back()->with('success', 'Potwierdzono zapoznanie się z wiadomością.');
    }
}
