<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{

    public static function optional(mixed $user): mixed
    {
        return $user === null ? null : new self($user);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'positions' => PositionResource::collection($this->whenLoaded('positions')),
        ];
    }
}
