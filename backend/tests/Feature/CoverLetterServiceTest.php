<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\JobData;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\CoverLetterService;
use Tests\TestCase;

class CoverLetterServiceTest extends TestCase
{
    public function test_generates_letter_with_matched_skills_and_no_placeholders(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Developer',
            'portfolio_url' => 'https://example.test/portfolio',
        ]);
        UserSetting::factory()->for($user)->create(['skills' => ['react', 'php', 'python']]);

        $job = new JobData(
            source: 'remoteok',
            externalId: '1',
            title: 'React Developer',
            company: 'Acme Remote',
            url: 'https://example.test/jobs/1',
            description: 'Build web applications with React and PHP.',
            locationText: 'Worldwide',
            tags: [],
            salaryMin: 70000.0,
            salaryMax: 90000.0,
            salaryCurrency: 'USD',
            postedAt: now(),
        );

        $letter = app(CoverLetterService::class)->generate($user, $job);

        $this->assertStringContainsString('Acme Remote', $letter);
        $this->assertStringContainsString('React Developer', $letter);
        $this->assertStringContainsString('react, php', $letter);
        $this->assertStringContainsString('https://example.test/portfolio', $letter);
        $this->assertStringContainsString('Test Developer', $letter);
        $this->assertStringNotContainsString('{company}', $letter);
        $this->assertStringNotContainsString('{matched_skills}', $letter);
    }
}
