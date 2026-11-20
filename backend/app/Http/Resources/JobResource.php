<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $posting = $this->jobPosting;

        return [
            'id' => $this->id,
            'title' => $posting->title,
            'company' => $posting->company,
            'url' => $posting->url,
            'location' => $posting->location_text,
            'salary_min' => $posting->salary_min,
            'salary_max' => $posting->salary_max,
            'salary_currency' => $posting->salary_currency,
            'source' => $posting->source,
            'score' => $this->score,
            'reasons' => $this->reasons,
            'status' => $this->status,
            'location_uncertain' => $this->location_uncertain,
            'salary_unknown' => $this->salary_unknown,
            'cover_letter' => $this->cover_letter,
            'notified_at' => $this->notified_at?->toIso8601String(),
        ];
    }
}
