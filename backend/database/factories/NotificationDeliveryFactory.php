<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserJob;
use App\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'user_job_id' => UserJob::factory(),
            'channel' => NotificationChannel::InApp,
            'status' => 'pending',
            'attempts' => 0,
            'sent_at' => null,
            'error' => null,
        ];
    }
}
