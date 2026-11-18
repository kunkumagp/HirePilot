<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\UserJob;
use Illuminate\Database\UniqueConstraintViolationException;
use Tests\TestCase;

class JobPostingTest extends TestCase
{
    public function test_rejects_duplicate_external_id_from_same_source(): void
    {
        JobPosting::factory()->create([
            'source' => 'remoteok',
            'external_id' => 'job-123',
        ]);

        $this->expectException(UniqueConstraintViolationException::class);

        JobPosting::factory()->create([
            'source' => 'remoteok',
            'external_id' => 'job-123',
        ]);
    }

    public function test_rejects_duplicate_user_job_for_same_user_and_posting(): void
    {
        $userJob = UserJob::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        UserJob::factory()->create([
            'user_id' => $userJob->user_id,
            'job_posting_id' => $userJob->job_posting_id,
        ]);
    }
}
