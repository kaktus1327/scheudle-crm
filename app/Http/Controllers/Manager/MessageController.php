<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manager\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PersonResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(): Response
    {
        $messages = Message::with('author')
            ->withCount([
                'recipients',
                'recipients as acknowledged_count' => fn ($q) => $q->whereNotNull('message_recipients.acknowledged_at'),
            ])
            ->latest()
            ->get();

        return Inertia::render('manager/messages/index', [
            'messages' => MessageResource::collection($messages),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('manager/messages/create', [
            'employees' => PersonResource::collection(User::employees()->orderBy('name')->get()),
        ]);
    }

    public function store(StoreMessageRequest $request): RedirectResponse
    {
        $message = DB::transaction(function () use ($request) {
            $message = Message::create([
                'author_id' => $request->user()->id,
                'title' => $request->validated('title'),
                'body' => $request->validated('body'),
                'requires_acknowledgement' => $request->boolean('requires_acknowledgement'),
            ]);

            $recipientIds = $request->validated('recipient_ids') ?: User::employees()->pluck('id')->all();
            $message->recipients()->attach($recipientIds);

            foreach ($request->file('attachments', []) as $file) {
                $message->attachments()->create([
                    'path' => $file->store("attachments/{$message->id}", 'local'),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            return $message;
        });

        return to_route('manager.messages.show', $message)->with('success', 'Wiadomość wysłana.');
    }

    public function show(Message $message): Response
    {
        $message->load(['author', 'attachments', 'recipients' => fn ($q) => $q->orderBy('name')]);

        return Inertia::render('manager/messages/show', [
            'message' => new MessageResource($message),
            'recipients' => $message->recipients->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'read_at' => $u->pivot->read_at,
                'acknowledged_at' => $u->pivot->acknowledged_at,
            ]),
        ]);
    }
}
