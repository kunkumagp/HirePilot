<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ResumeVersion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'resume_id',
        'version_number',
        'is_current',
        'original_filename',
        'file_path',
        'file_type',
        'file_size',
        'parsed_content',
        'raw_text',
        'parse_status',
        'parse_attempts',
        'parse_error',
    ];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
            'file_size' => 'integer',
            'version_number' => 'integer',
            'parse_attempts' => 'integer',
            'parsed_content' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ResumeVersion $version) {
            if (empty($version->uuid)) {
                $version->uuid = (string) Str::uuid();
            }
        });
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
