<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'portfolio_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'skills' => ['sometimes', 'array'],
            'title_include' => ['sometimes', 'array'],
            'exclude_words' => ['sometimes', 'array'],
            'location_accept' => ['sometimes', 'array'],
            'location_reject' => ['sometimes', 'array'],
            'max_age_days' => ['sometimes', 'integer', 'min:1'],
            'min_salary_usd_yearly' => ['sometimes', 'integer', 'min:0'],
            'allow_unknown_salary' => ['sometimes', 'boolean'],
            'min_score' => ['sometimes', 'integer'],
            'weights' => ['sometimes', 'array'],
            'max_notifications_per_run' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
