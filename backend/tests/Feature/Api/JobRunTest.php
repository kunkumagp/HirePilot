<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\JobPosting;
use App\Models\User;
use App\Models\UserJob;
use App\Services\Sources\RemoteOkSource;
use App\Services\UserSettingsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobRunTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    private function fixturePayload(): array
    {
        $contents = file_get_contents(storage_path('app/fixtures/remoteok.json'));

        return (array) json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_authenticated_user_can_run_the_job_search(): void
    {
        config([
            'jobhunter.sources' => [
                'remoteok' => ['enabled' => true, 'adapter' => RemoteOkSource::class],
            ],
        ]);
        Http::preventStrayRequests();
        Http::fake(['remoteok.com/api' => Http::response($this->fixturePayload())]);

        $user = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($user);

        $response = $this->withToken($user->createToken('spa')->plainTextToken)
            ->postJson('/api/jobs/run')
            ->assertOk()
            ->assertJsonPath('fetched', 2);

        $response->assertJsonStructure(['sources', 'users', 'matches', 'new', 'notified']);

        $this->assertSame(2, JobPosting::count());
        $this->assertSame(1, UserJob::where('user_id', $user->id)->count());
    }

    public function test_guest_cannot_run_the_job_search(): void
    {
        $this->postJson('/api/jobs/run')->assertUnauthorized();
    }
}
