<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_register_creates_user_with_default_settings_and_returns_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New Developer',
            'email' => 'new@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $this->assertSame(1, User::count());
        $this->assertSame(1, UserSetting::count());
        $this->assertTrue(Hash::check('password123', User::firstOrFail()->password));
    }

    public function test_register_rejects_invalid_payload(): void
    {
        $this->postJson('/api/register', [
            'name' => 'New Developer',
            'email' => 'not-an-email',
            'password' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.test',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token', 'user' => ['id']]);
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password123')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_protected_endpoint_rejects_unauthenticated_request(): void
    {
        $this->getJson('/api/jobs')->assertUnauthorized();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('spa')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertNoContent();

        $this->assertSame(0, $user->tokens()->count());
    }
}
