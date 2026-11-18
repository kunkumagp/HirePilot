<?php

declare(strict_types=1);

namespace App\Models;

use App\NotificationChannel;
use Database\Factories\NotificationDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    /** @use HasFactory<NotificationDeliveryFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'user_job_id',
        'channel',
        'status',
        'attempts',
        'sent_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userJob(): BelongsTo
    {
        return $this->belongsTo(UserJob::class);
    }
}
