<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role->value,
            'role_label' => $this->role->label(),
            'hourly_rate' => (float) $this->hourly_rate,
            'must_change_password' => $this->must_change_password,
            'deleted_at' => $this->deleted_at?->toDateString(),
            'positions' => PositionResource::collection($this->whenLoaded('positions')),
            'documents' => UserDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
