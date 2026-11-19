<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserChannel;
use App\NotificationChannel;
use App\Services\UserSettingsService;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    public function test_preference_options_come_from_config(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/settings/options')
            ->assertOk()
            ->assertJsonStructure([
                'fields' => [['key', 'label']],
                'experience_levels',
                'arrangements',
            ])
            ->assertJsonFragment(['key' => 'web_development', 'label' => 'Web Development']);
    }

    public function test_returns_settings_and_channels_without_exposing_credentials(): void
    {
        $user = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($user);
        UserChannel::factory()->for($user)->create([
            'type' => NotificationChannel::Email,
            'credentials' => Crypt::encryptString('{"smtp_password":"secret"}'),
        ]);

        $response = $this->withToken($user->createToken('spa')->plainTextToken)
            ->getJson('/api/settings');

        $response->assertOk()
            ->assertJsonPath('settings.min_score', 6)
            ->assertJsonPath('channels.0.type', 'email')
            ->assertJsonMissingPath('channels.0.credentials');
    }

    public function test_update_changes_profile_and_settings(): void
    {
        $user = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($user);

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->putJson('/api/settings', [
                'name' => 'Updated Name',
                'skills' => ['laravel', 'php'],
                'min_score' => 8,
            ])
            ->assertOk()
            ->assertJsonPath('user.name', 'Updated Name');

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame(8, $user->userSetting->fresh()->min_score);
        $this->assertSame(['laravel', 'php'], $user->userSetting->fresh()->skills);
    }

    public function test_preferences_can_be_updated_through_the_api(): void
    {
        $user = User::factory()->create();
        app(UserSettingsService::class)->createDefaults($user);

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->putJson('/api/settings', [
                'field' => 'sales',
                'field_keywords' => ['hubspot', 'pipeline'],
                'experience_level' => 'senior',
                'min_years_experience' => 5,
                'work_arrangement' => 'hybrid',
                'preferred_countries' => ['Asia', 'Sri Lanka'],
                'salary_currency' => 'EUR',
            ])
            ->assertOk();

        $settings = $user->userSetting->fresh();
        $this->assertSame('sales', $settings->field);
        $this->assertSame(['hubspot', 'pipeline'], $settings->field_keywords);
        $this->assertSame('senior', $settings->experience_level);
        $this->assertSame(5, $settings->min_years_experience);
        $this->assertSame('hybrid', $settings->work_arrangement);
        $this->assertSame(['Asia', 'Sri Lanka'], $settings->preferred_countries);
        $this->assertSame('EUR', $settings->salary_currency);
    }

    public function test_channel_credentials_are_stored_encrypted(): void
    {
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('spa')->plainTextToken)
            ->postJson('/api/channels', [
                'type' => 'email',
                'target' => 'candidate@example.test',
                'credentials' => ['smtp_host' => 'smtp.example.test'],
            ]);

        $response->assertCreated()->assertJsonMissingPath('channel.credentials');

        $channel = $user->userChannels()->firstOrFail();
        $this->assertStringNotContainsString('smtp.example.test', $channel->credentials);
        $this->assertSame(
            ['smtp_host' => 'smtp.example.test'],
            json_decode(Crypt::decryptString($channel->credentials), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function test_channel_update_without_credentials_keeps_existing_credentials(): void
    {
        $user = User::factory()->create();
        UserChannel::factory()->for($user)->create([
            'type' => NotificationChannel::Email,
            'credentials' => Crypt::encryptString('{"smtp_password":"secret"}'),
        ]);

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->postJson('/api/channels', [
                'type' => 'email',
                'enabled' => false,
                'target' => 'new@example.test',
            ])
            ->assertCreated();

        $channel = $user->userChannels()->firstOrFail();
        $this->assertFalse($channel->enabled);
        $this->assertSame('new@example.test', $channel->target);
        $this->assertSame(
            ['smtp_password' => 'secret'],
            json_decode(Crypt::decryptString($channel->credentials), true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function test_cannot_delete_another_users_channel(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $channel = UserChannel::factory()->for($other)->create();

        $this->withToken($user->createToken('spa')->plainTextToken)
            ->deleteJson('/api/channels/'.$channel->id)
            ->assertNotFound();

        $this->assertNotNull(UserChannel::find($channel->id));
    }
}
