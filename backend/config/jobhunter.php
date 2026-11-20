<?php

declare(strict_types=1);

use App\Services\Sources\HimalayasSource;
use App\Services\Sources\JobicySource;
use App\Services\Sources\RemoteOkSource;
use App\Services\Sources\RemotiveSource;
use App\Services\Sources\WeWorkRemotelySource;

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
    | Per-user job preferences
    |--------------------------------------------------------------------------
    |
    | Field presets keep the UI simple while custom keywords let any user
    | search a niche role. Experience and arrangement are matched from job text.
    |
    */

    'fields' => [
        'web_development' => ['web', 'frontend', 'backend', 'full stack', 'fullstack', 'react', 'node', 'laravel', 'php', 'javascript', 'typescript'],
        'sales' => ['sales', 'account executive', 'business development', 'bdr', 'sdr', 'account manager'],
        'marketing' => ['marketing', 'growth', 'seo', 'content', 'demand generation', 'email marketing', 'hubspot'],
        'design' => ['design', 'ui', 'ux', 'figma', 'graphic', 'product designer'],
        'data' => ['data', 'analytics', 'bi', 'sql', 'python', 'machine learning'],
        'support' => ['support', 'customer success', 'help desk', 'customer support'],
        'devops' => ['devops', 'cloud', 'infrastructure', 'kubernetes', 'aws'],
    ],

    'preferences' => [
        'field' => 'web_development',
        'job_query' => null,
        'job_category' => null,
        'job_subcategory' => null,
        'field_keywords' => [],
        'experience_level' => 'any',
        'min_years_experience' => null,
        'work_arrangement' => 'any',
        'preferred_countries' => [],
        'salary_currency' => 'USD',
    ],

    /*
    | Free-text job query: the user types the exact roles they want,
    | e.g. "React Developer, HubSpot Marketing Manager".
    */
    'job_query' => [
        'stop_words' => [
            'developer', 'engineer', 'remote', 'job', 'role', 'position', 'work',
            'with', 'and', 'for', 'senior', 'junior', 'team', 'company', 'years',
            'experience', 'anywhere', 'worldwide', 'looking', 'hiring',
        ],
        'max_token_points' => 3,
    ],

    'experience_levels' => [
        'any' => [],
        'junior' => ['junior', 'entry level', 'graduate', 'associate'],
        'mid' => ['mid level', 'intermediate', '2+ years', '3+ years'],
        'senior' => ['senior', 'sr.', 'staff', '5+ years'],
        'lead' => ['lead', 'principal', 'head of', 'director'],
    ],

    'arrangements' => [
        'any' => [],
        'remote' => ['remote', 'anywhere', 'worldwide', 'distributed'],
        'hybrid' => ['hybrid', 'partially remote'],
        'onsite' => ['onsite', 'on-site', 'in-office', 'office based'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring
    |--------------------------------------------------------------------------
    */

    'scoring' => [
        // A field match alone (6) is enough; generic bonuses max out at 5.
        'min_score' => 6,
        'weights' => [
            'title_skill' => 3,
            'desc_skill' => 1,
            'worldwide' => 2,
            'usd_salary' => 2,
            'fresh_24h' => 1,
            'location_uncertain' => -2,
            'field_match' => 6,
            'query_match' => 6,
            'query_token' => 1,
            'experience_match' => 2,
            'years_match' => 2,
            'arrangement_match' => 2,
            'country_match' => 2,
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
        'remotive' => ['enabled' => true, 'adapter' => RemotiveSource::class],
        'weworkremotely' => ['enabled' => true, 'adapter' => WeWorkRemotelySource::class],
        'jobicy' => ['enabled' => true, 'adapter' => JobicySource::class],
        'himalayas' => ['enabled' => true, 'adapter' => HimalayasSource::class],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notify' => [
        'max_notifications_per_run' => 10,
        'default_channels' => ['telegram'],
        'retry_attempts' => 3,
        'retry_backoff_ms' => 200,
        // Local/Actions run synchronously; SaaS workers set this to true.
        'use_queue' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan limits (placeholder — no payment integration yet)
    |--------------------------------------------------------------------------
    */

    'plans' => [
        'free' => ['max_notifications_per_run' => 10],
        'pro' => ['max_notifications_per_run' => 100],
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
