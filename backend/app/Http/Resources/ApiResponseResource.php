<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'success' => true,
            'message' => $this->resource['message'] ?? 'Request completed successfully.',
            'data' => $this->resource['data'] ?? [],
        ];
    }
}
