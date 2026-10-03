<?php

namespace App\Http\Resources;

use App\Models\ShiftTrade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftTradeResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'note' => $this->note,
            'availability_conflict' => $this->when($user?->isManager(), $this->availability_conflict),
            'requester' => PersonResource::optional($this->whenLoaded('requester')),
            'target_user' => PersonResource::optional($this->whenLoaded('targetUser')),
            'claimer' => PersonResource::optional($this->whenLoaded('claimer')),
            'offered_shift' => new ShiftResource($this->whenLoaded('offeredShift')),
            'requested_shift' => $this->whenLoaded('requestedShift', fn () => $this->requestedShift ? new ShiftResource($this->requestedShift) : null),
            'created_at' => $this->created_at->toIso8601String(),
            'can' => [
                'respond' => $user?->can('respond', $this->resource) ?? false,
                'claim' => $user?->can('claim', $this->resource) ?? false,
                'cancel' => $user?->can('cancel', $this->resource) ?? false,
                'decide' => $user?->can('decide', $this->resource) ?? false,
            ],
        ];
    }
}
