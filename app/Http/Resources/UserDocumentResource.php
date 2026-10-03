<?php

namespace App\Http\Resources;

use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDocumentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->whenLoaded('type', fn () => ['id' => $this->type->id, 'name' => $this->type->name]),
            'user' => new PersonResource($this->whenLoaded('user')),
            'valid_until' => $this->valid_until->toDateString(),
            'days_left' => $this->daysLeft(),
            'expiring_soon' => $this->relationLoaded('type') && $this->isExpiringSoon(),
            'note' => $this->note,
        ];
    }
}
