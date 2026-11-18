<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jobhunter:run {--dry-run : Run the whole pipeline without sending notifications or marking them as notified}')]
#[Description('Fetch, filter, score, store and notify about matching remote jobs.')]
class JobHunterRunCommand extends Command
{
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        /** @var array<string, array{enabled: bool}> $sources */
        $sources = config('jobhunter.sources', []);
        $enabledSources = collect($sources)
            ->filter(fn (array $settings): bool => (bool) ($settings['enabled'] ?? false))
            ->keys()
            ->all();

        $this->info('HirePilot — job run');
        $this->line('Mode: '.($isDryRun ? 'DRY RUN (no notifications will be sent)' : 'LIVE'));
        $this->line('Sources enabled: '.count($enabledSources).' ('.implode(', ', $enabledSources).')');
        $this->line('Fetched: 0');
        $this->line('After filters: 0');
        $this->line('New: 0');
        $this->line('Notified: 0');
        $this->comment('Milestone 1 skeleton: source adapters arrive in Milestone 2.');

        return self::SUCCESS;
    }
}
