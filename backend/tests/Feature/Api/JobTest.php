<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\JobPosting;
use App\Models\User;
use App\Models\UserJob;
use Tests\TestCase;

class JobTest extends TestCase
{
    public function test_lists_only_the_authenticated_users_jobs(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $posting = JobPosting::factory()->create();

        UserJob::factory()->for($user)->create([
            'job_posting_id' => $posting->id,
            'score' => 9,
        ]);
        UserJob::factory()->for($other)->create(['score' => 3]);

        $response = $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/jobs');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.score', 9);
    }
}
