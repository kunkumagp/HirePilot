<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Resume extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Resume $resume) {
            if (empty($resume->uuid)) {
                $resume->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function (Resume $resume) {
            if ($resume->isForceDeleting()) {
                return;
            }
            $resume->versions()->delete();
        });

        static::restored(function (Resume $resume) {
            $resume->versions()->withTrashed()->restore();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResumeVersion::class);
    }

    public function currentVersion(): HasMany
    {
        return $this->hasMany(ResumeVersion::class)->where('is_current', true)->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)->firstOrFail();
    }
}
