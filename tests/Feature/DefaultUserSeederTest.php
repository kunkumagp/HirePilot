<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSetting;
use Database\Seeders\DefaultUserSeeder;
use Tests\TestCase;

class DefaultUserSeederTest extends TestCase
{
    public function test_seeder_creates_active_default_user_with_configured_settings(): void
    {
        $this->seed(DefaultUserSeeder::class);

        $user = User::where('email', config('jobhunter.profile.email'))->firstOrFail();

        $this->assertSame(config('jobhunter.profile.name'), $user->name);
        $this->assertSame(config('jobhunter.profile.portfolio_url'), $user->portfolio_url);
        $this->assertTrue($user->is_active);
        $this->assertSame(config('jobhunter.profile.skills'), $user->userSetting->skills);
        $this->assertSame(config('jobhunter.filters.max_age_days'), $user->userSetting->max_age_days);
        $this->assertSame(config('jobhunter.scoring.min_score'), $user->userSetting->min_score);
    }

    public function test_seeder_does_not_duplicate_user_or_settings_when_run_twice(): void
    {
        $this->seed(DefaultUserSeeder::class);
        $this->seed(DefaultUserSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertSame(1, UserSetting::count());
    }
}
