<?php

namespace App\Http\Resources;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'excerpt' => str($this->body)->limit(120)->toString(),
            'requires_acknowledgement' => $this->requires_acknowledgement,
            'author' => PersonResource::optional($this->whenLoaded('author')),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'size' => $a->size,
                'url' => route('attachments.download', $a),
            ])),
            'read_at' => $this->pivot?->read_at,
            'acknowledged_at' => $this->pivot?->acknowledged_at,
            'recipients_count' => $this->whenCounted('recipients'),
            'acknowledged_count' => $this->whenHas('acknowledged_count'),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
