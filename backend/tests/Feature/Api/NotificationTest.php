<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\JobMatchNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    public function test_lists_and_marks_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new JobMatchNotification(['title' => 'React Developer @ Acme', 'body' => 'Score: 9']));

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data.title', 'React Developer @ Acme');

        $id = $user->notifications()->firstOrFail()->id;

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->patchJson('/api/notifications/'.$id.'/read')
            ->assertNoContent();

        $this->assertNotNull($user->notifications()->firstOrFail()->read_at);
    }

    public function test_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $other->notify(new JobMatchNotification(['title' => 'Private']));
        $id = $other->notifications()->firstOrFail()->id;

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->patchJson('/api/notifications/'.$id.'/read')
            ->assertNotFound();
    }
}
