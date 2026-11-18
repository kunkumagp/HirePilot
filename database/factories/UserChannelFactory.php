<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserChannel;
use App\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserChannel>
 */
class UserChannelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => NotificationChannel::InApp,
            'enabled' => true,
            'target' => null,
            'credentials' => null,
        ];
    }
}
