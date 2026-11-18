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

    protected function casts(): array
    {
        return [
            'skills' => 'array',
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
