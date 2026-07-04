<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        Notification::assertSentTo(
            User::where('email', 'john@example.com')->first(),
            VerifyEmail::class
        );
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token', 'token_expires_at'],
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'profile'],
            ])
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/profile', [
            'name' => 'Updated Name',
            'headline' => 'Software Engineer',
            'location' => 'New York',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'headline' => 'Software Engineer',
            'location' => 'New York',
        ]);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass1'),
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/password', [
            'current_password' => 'CurrentPass1',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password changed successfully.',
            ]);
    }

    public function test_user_cannot_change_password_with_wrong_current(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CurrentPass1'),
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/password', [
            'current_password' => 'WrongPass',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ]);
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
        ]);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/account', [
            'password' => 'Password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Account deleted.',
            ]);

        $this->assertSoftDeleted($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    public function test_user_can_list_sessions(): void
    {
        $user = User::factory()->create();
        $user->createToken('Chrome on Windows')->plainTextToken;
        $user->createToken('Safari on iPhone')->plainTextToken;
        $currentToken = $user->createToken('Current Session')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $currentToken,
        ])->getJson('/api/sessions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_revoke_session(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('Session 1')->plainTextToken;
        $token2 = $user->createToken('Session 2')->plainTextToken;

        $token1Model = $user->tokens()->where('name', 'Session 1')->first();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
        ])->deleteJson('/api/sessions/' . $token1Model->id);

        $response->assertStatus(200);

        $this->assertNull($user->tokens()->where('id', $token1Model->id)->first());
    }

    public function test_user_cannot_revoke_current_session(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Current Session')->plainTextToken;
        $currentTokenId = $user->tokens()->first()->id;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/sessions/' . $currentTokenId);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot revoke current session. Use logout instead.',
            ]);
    }

    public function test_forgot_password_sends_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_does_not_reveal_unregistered_email(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['service', 'timestamp'],
            ]);
    }
}
