<?php

declare(strict_types=1);

use App\Services\Sources\RemoteOkSource;

return [
    /*
    |--------------------------------------------------------------------------
    | Default profile
    |--------------------------------------------------------------------------
    |
    | Used to seed the local default user only. In SaaS mode each user overrides
    | these values through the settings UI and the user_settings table.
    |
    */

    'profile' => [
        'name' => 'My Name',
        'email' => 'default@example.test',
        'portfolio_url' => 'https://example.com',
        'skills' => ['react', 'node', 'typescript', 'laravel', 'php', 'javascript', 'tailwind'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [
        'max_age_days' => 14,
        'title_include' => ['web', 'frontend', 'backend', 'full stack', 'fullstack', 'react', 'node', 'laravel'],
        'exclude_words' => ['intern', 'unpaid', 'principal', 'senior staff', 'security clearance'],
        'location_accept' => ['worldwide', 'anywhere', 'global', 'asia', 'apac', 'sri lanka'],
        'location_reject' => ['us only', 'usa only', 'eu only', 'uk only', 'must reside'],
        'min_salary_usd_yearly' => 24000,
        'allow_unknown_salary' => true,
        // Sources rarely state the pay period, so infer it from the amount.
        'salary' => [
            'hourly_threshold' => 1000,
            'monthly_threshold' => 12000,
            'hours_per_year' => 2080,
            'months_per_year' => 12,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring
    |--------------------------------------------------------------------------
    */

    'scoring' => [
        'min_score' => 5,
        'weights' => [
            'title_skill' => 3,
            'desc_skill' => 1,
            'worldwide' => 2,
            'usd_salary' => 2,
            'fresh_24h' => 1,
            'location_uncertain' => -2,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sources
    |--------------------------------------------------------------------------
    */

    'sources' => [
        // Adding a source needs one adapter file plus one line here.
        'remoteok' => ['enabled' => true, 'adapter' => RemoteOkSource::class],
        'remotive' => ['enabled' => true],
        'weworkremotely' => ['enabled' => true],
        'jobicy' => ['enabled' => true],
        'himalayas' => ['enabled' => true],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notify' => [
        'max_notifications_per_run' => 10,
        'default_channels' => ['telegram'],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => 12,
        'retries' => 3,
        'user_agent' => env('JOBHUNTER_USER_AGENT', 'HirePilot/1.0 (+https://example.com/bot)'),
    ],
];
