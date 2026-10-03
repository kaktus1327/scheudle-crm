<?php

namespace App\Http\Resources;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week' => $this->week()->toArray(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'published_at' => $this->published_at?->toIso8601String(),
            'template' => $this->whenLoaded('template', fn () => $this->template?->only(['id', 'name'])),
            'shifts' => ShiftResource::collection($this->whenLoaded('shifts')),
            'shifts_count' => $this->whenCounted('shifts'),
        ];
    }
}
