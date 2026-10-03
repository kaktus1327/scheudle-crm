<?php

namespace App\Http\Resources;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'start_time' => substr($this->start_time, 0, 5),
            'end_time' => substr($this->end_time, 0, 5),
            'note' => $this->note,
            'hours' => $this->durationInHours(),
            'position' => new PositionResource($this->whenLoaded('position')),
            'user' => PersonResource::optional($this->whenLoaded('user')),
            'original_user' => PersonResource::optional($this->whenLoaded('originalUser')),
            'is_open' => $this->isOpen(),
            'was_traded' => $this->original_user_id !== null && $this->original_user_id !== $this->user_id,
        ];
    }
}
