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
            'field' => ['sometimes', 'nullable', 'string', 'max:64'],
            'job_query' => ['sometimes', 'nullable', 'string', 'max:500'],
            'job_category' => ['sometimes', 'nullable', 'string', 'max:64'],
            'job_subcategory' => ['sometimes', 'nullable', 'string', 'max:64'],
            'field_keywords' => ['sometimes', 'array'],
            'experience_level' => ['sometimes', 'string', 'in:any,junior,mid,senior,lead'],
            'min_years_experience' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:50'],
            'work_arrangement' => ['sometimes', 'string', 'in:any,remote,hybrid,onsite'],
            'preferred_countries' => ['sometimes', 'array'],
            'salary_currency' => ['sometimes', 'string', 'size:3'],
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
