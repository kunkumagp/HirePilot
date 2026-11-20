<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\UserSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DefaultUserSeeder extends Seeder
{
    public function run(UserSettingsService $settings): void
    {
        /** @var array<string, mixed> $profile */
        $profile = config('jobhunter.profile');

        $user = User::firstOrCreate(
            ['email' => $profile['email']],
            [
                'name' => $profile['name'],
                'portfolio_url' => $profile['portfolio_url'],
                'password' => Hash::make(Str::random(32)),
                'is_active' => true,
            ],
        );

        $settings->createDefaults($user);
    }
}
