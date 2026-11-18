<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserJob;

final class NotificationMessage
{
    /**
     * @return array{title: string, body: string}
     */
    public function build(UserJob $userJob): array
    {
        $job = $userJob->jobPosting;

        $title = sprintf('%s @ %s', $job->title, $job->company);

        $salary = $userJob->salary_unknown || $job->salary_currency === null
            ? 'salary not listed'
            : sprintf('%s %s-%s', $job->salary_currency, $job->salary_min ?? '?', $job->salary_max ?? '?');

        $location = $job->location_text !== '' ? $job->location_text : 'unknown';
        $warning = $userJob->location_uncertain ? '   ⚠️ (location unclear)' : '';

        /** @var list<string> $reasons */
        $reasons = is_array($userJob->reasons) ? $userJob->reasons : [];

        $body = implode("\n", [
            sprintf('🔥 %s', $title),
            sprintf('💰 %s', $salary),
            sprintf('🌍 %s%s', $location, $warning),
            sprintf('⭐ Score: %d  (%s)', $userJob->score, implode('; ', $reasons)),
            sprintf('🔗 %s', $job->url),
            sprintf('Source: %s', $job->source),
        ]);

        return ['title' => $title, 'body' => $body];
    }
}
