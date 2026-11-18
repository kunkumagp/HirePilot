<?php

declare(strict_types=1);

namespace App\Models;

use App\NotificationChannel;
use Database\Factories\UserChannelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserChannel extends Model
{
    /** @use HasFactory<UserChannelFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'type',
        'enabled',
        'target',
        'credentials',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => NotificationChannel::class,
            'enabled' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
