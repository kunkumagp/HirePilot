<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\Models\JobPosting;
use App\Models\User;
use App\Models\UserJob;
use App\Services\UserPipelineService;
use App\Services\UserSettingsService;
use Tests\TestCase;

class UserPipelineServiceTest extends TestCase
{
    /**
     * @return list<JobData>
     */
    private function jobs(): array
    {
        $react = JobPosting::factory()->create([
            'source' => 'remoteok',
            'external_id' => 'job-react',
            'title' => 'React Developer',
        ]);
        $laravel = JobPosting::factory()->create([
            'source' => 'jobicy',
            'external_id' => 'job-laravel',
            'title' => 'Laravel Developer',
        ]);

        return [
            new JobData(
                source: $react->source,
                externalId: $react->external_id,
                title: $react->title,
                company: $react->company,
                url: $react->url,
                description: 'Work with React.',
                locationText: 'Worldwide',
                tags: [],
                salaryMin: 70000.0,
                salaryMax: 90000.0,
                salaryCurrency: 'USD',
                postedAt: now(),
            ),
            new JobData(
                source: $laravel->source,
                externalId: $laravel->external_id,
                title: $laravel->title,
                company: $laravel->company,
                url: $laravel->url,
                description: 'Work with Laravel.',
                locationText: 'Worldwide',
                tags: [],
                salaryMin: 70000.0,
                salaryMax: 90000.0,
                salaryCurrency: 'USD',
                postedAt: now(),
            ),
        ];
    }

    /**
     * @param  array{matches: array}  $result
     */
    private function scoreFor(array $result, string $title): int
    {
        foreach ($result['matches'] as $match) {
            if ($match['job']->title === $title) {
                return $match['score']->score;
            }
        }

        return -1;
    }

    public function test_each_user_gets_only_jobs_matching_their_own_rules(): void
    {
        $reactUser = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($reactUser);
        $reactUser->userSetting->update([
            'skills' => ['react'],
            'field' => 'design',
            'field_keywords' => ['react'],
        ]);

        $laravelUser = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($laravelUser);
        $laravelUser->userSetting->update([
            'skills' => ['laravel'],
            'field' => 'sales',
            'field_keywords' => ['laravel'],
        ]);

        $jobs = $this->jobs();
        $service = app(UserPipelineService::class);

        $reactResult = $service->run($reactUser, $jobs, true);
        $laravelResult = $service->run($laravelUser, $jobs, true);

        // Both jobs pass the hard filters; each user's own rules decide the ranking.
        $this->assertGreaterThan(
            $this->scoreFor($reactResult, 'Laravel Developer'),
            $this->scoreFor($reactResult, 'React Developer'),
        );
        $this->assertGreaterThan(
            $this->scoreFor($laravelResult, 'React Developer'),
            $this->scoreFor($laravelResult, 'Laravel Developer'),
        );

        $this->assertSame(1, UserJob::where('user_id', $reactUser->id)->count());
        $this->assertSame(1, UserJob::where('user_id', $laravelUser->id)->count());
    }
}
