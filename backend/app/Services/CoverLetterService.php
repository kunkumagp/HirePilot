<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\JobData;
use App\Models\User;
use Illuminate\Support\Str;

final class CoverLetterService
{
    public function generate(User $user, JobData $job): string
    {
        $template = (string) file_get_contents(resource_path('views/cover_letter.md.tpl'));

        /** @var list<string> $skills */
        $skills = $user->userSetting?->skills ?? config('jobhunter.profile.skills', []);
        $matched = [];

        foreach ($skills as $skill) {
            if ($skill !== '' && Str::contains($job->title.' '.$job->description, $skill, true)) {
                $matched[] = $skill;
            }
        }

        return strtr($template, [
            '{company}' => $job->company !== '' ? $job->company : 'your team',
            '{job_title}' => $job->title,
            '{matched_skills}' => $matched !== [] ? implode(', ', $matched) : 'web development',
            '{my_name}' => $user->name,
            '{portfolio_url}' => (string) ($user->portfolio_url ?? config('jobhunter.profile.portfolio_url', '')),
        ]);
    }
}
