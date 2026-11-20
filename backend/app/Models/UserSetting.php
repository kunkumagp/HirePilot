<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    /** @use HasFactory<UserSettingFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'job_query',
        'skills',
        'field',
        'job_category',
        'job_subcategory',
        'field_keywords',
        'experience_level',
        'min_years_experience',
        'work_arrangement',
        'preferred_countries',
        'salary_currency',
        'title_include',
        'exclude_words',
        'location_accept',
        'location_reject',
        'max_age_days',
        'min_salary_usd_yearly',
        'allow_unknown_salary',
        'min_score',
        'weights',
        'max_notifications_per_run',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'field_keywords' => 'array',
            'preferred_countries' => 'array',
            'min_years_experience' => 'integer',
            'title_include' => 'array',
            'exclude_words' => 'array',
            'location_accept' => 'array',
            'location_reject' => 'array',
            'weights' => 'array',
            'allow_unknown_salary' => 'boolean',
            'max_age_days' => 'integer',
            'min_salary_usd_yearly' => 'integer',
            'min_score' => 'integer',
            'max_notifications_per_run' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
