<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserJobFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserJob extends Model
{
    /** @use HasFactory<UserJobFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'job_posting_id',
        'score',
        'reasons',
        'status',
        'location_uncertain',
        'salary_unknown',
        'cover_letter',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'reasons' => 'array',
            'location_uncertain' => 'boolean',
            'salary_unknown' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function notificationDeliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }
}
