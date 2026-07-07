<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'is_active' => $this->is_active,
            'current_version' => $this->whenLoaded('currentVersion', function () {
                $version = $this->currentVersion->first();
                return $version ? new ResumeVersionResource($version) : null;
            }),
            'version_count' => $this->whenCounted('versions'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
