<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'headline' => $this->headline,
            'bio' => $this->bio,
            'location' => $this->location,
            'linkedin_url' => $this->linkedin_url,
            'github_url' => $this->github_url,
            'website_url' => $this->website_url,
            'preferences' => $this->preferences,
        ];
    }
}
