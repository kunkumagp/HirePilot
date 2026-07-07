<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'version_number' => $this->version_number,
            'is_current' => $this->is_current,
            'original_filename' => $this->original_filename,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'parse_status' => $this->parse_status,
            'parsed_content' => $this->parsed_content,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
