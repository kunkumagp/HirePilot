<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\JobPosting;
use App\Models\User;
use App\Models\UserJob;
use App\Services\UserSettingsService;
use Tests\TestCase;

class JobDetailTest extends TestCase
{
    private function userWithJob(string $name = 'Alice'): array
    {
        $user = User::factory()->create(['name' => $name]);
        app(UserSettingsService::class)->createDefaults($user);
        $user->userSetting->update(['skills' => ['react'], 'field' => 'web_development']);

        $posting = JobPosting::factory()->create([
            'title' => 'React Developer',
            'company' => 'Acme Remote',
            'description' => 'Full job description with React and PHP.',
        ]);
        $job = UserJob::factory()->for($user)->create([
            'job_posting_id' => $posting->id,
            'score' => 9,
            'cover_letter' => 'OLD LETTER',
        ]);

        return [$user, $job];
    }

    public function test_show_returns_full_job_details(): void
    {
        [$user, $job] = $this->userWithJob();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/jobs/'.$job->id)
            ->assertOk()
            ->assertJsonPath('title', 'React Developer')
            ->assertJsonPath('company', 'Acme Remote')
            ->assertJsonPath('description', 'Full job description with React and PHP.')
            ->assertJsonPath('score', 9)
            ->assertJsonPath('cover_letter', 'OLD LETTER')
            ->assertJsonStructure(['url', 'source', 'reasons', 'status', 'tags', 'posted_at']);
    }

    public function test_cannot_view_another_users_job(): void
    {
        [, $job] = $this->userWithJob();
        $other = User::factory()->create();

        $this->withToken($other->createToken('spa')->plainTextToken)
            ->getJson('/api/jobs/'.$job->id)
            ->assertNotFound();
    }

    public function test_regenerate_cover_letter_uses_current_profile_and_saves_it(): void
    {
        [$user, $job] = $this->userWithJob('Alice Developer');

        $response = $this->withToken($user->createToken('spa')->plainTextToken)
            ->postJson('/api/jobs/'.$job->id.'/cover-letter')
            ->assertOk();

        $letter = (string) $response->json('cover_letter');

        $this->assertStringContainsString('Alice Developer', $letter);
        $this->assertStringContainsString('Acme Remote', $letter);
        $this->assertStringContainsString('React Developer', $letter);
        $this->assertSame(
            $response->json('cover_letter'),
            $job->fresh()->cover_letter,
        );
    }

    public function test_cannot_regenerate_another_users_cover_letter(): void
    {
        [, $job] = $this->userWithJob();
        $other = User::factory()->create();

        $this->withToken($other->createToken('spa')->plainTextToken)
            ->postJson('/api/jobs/'.$job->id.'/cover-letter')
            ->assertNotFound();
    }
}
