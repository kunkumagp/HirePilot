<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'posted_at' => 'datetime',
            'first_seen_at' => 'datetime',
        ];
    }

    public function userJobs(): HasMany
    {
        return $this->hasMany(UserJob::class);
    }
}
