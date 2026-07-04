<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'timezone' => ['sometimes', 'string', 'timezone'],
            'phone' => ['sometimes', 'string', 'max:30'],
            'headline' => ['sometimes', 'string', 'max:255'],
            'bio' => ['sometimes', 'string', 'max:1000'],
            'location' => ['sometimes', 'string', 'max:255'],
            'linkedin_url' => ['sometimes', 'url', 'max:255'],
            'github_url' => ['sometimes', 'url', 'max:255'],
            'website_url' => ['sometimes', 'url', 'max:255'],
            'preferences' => ['sometimes', 'json'],
        ];
    }
}
