<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_used_at' => $this->last_used_at?->diffForHumans(),
            'created_at' => $this->created_at?->toISOString(),
            'is_current' => $this->id === $request->user()?->currentAccessToken()?->id,
        ];
    }
}
