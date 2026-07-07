<?php

namespace Tests\Feature\Resume;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    private function headers(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_user_can_list_resumes(): void
    {
        Resume::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/resumes', $this->headers());

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_resume(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/resumes', [
            'title' => 'Software Engineer CV',
            'file' => $file,
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Resume created successfully.',
            ])
            ->assertJsonStructure([
                'data' => ['uuid', 'title', 'current_version'],
            ]);

        $this->assertDatabaseHas('resumes', [
            'user_id' => $this->user->id,
            'title' => 'Software Engineer CV',
        ]);
    }

    public function test_user_cannot_exceed_max_resumes(): void
    {
        Resume::factory()->count(5)->create(['user_id' => $this->user->id]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/resumes', [
            'title' => 'Extra Resume',
            'file' => $file,
        ], $this->headers());

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Maximum of 5 resumes reached.',
            ]);
    }

    public function test_user_can_view_resume_detail(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/resumes/{$resume->uuid}", $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('data.resume.uuid', $resume->uuid);
    }

    public function test_user_cannot_view_others_resume(): void
    {
        $otherUser = User::factory()->create();
        $resume = Resume::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/resumes/{$resume->uuid}", $this->headers());

        $response->assertStatus(403);
    }

    public function test_user_can_update_resume(): void
    {
        $resume = Resume::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Title',
        ]);

        $response = $this->putJson("/api/resumes/{$resume->uuid}", [
            'title' => 'New Title',
        ], $this->headers());

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'New Title');
    }

    public function test_user_can_delete_resume(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/resumes/{$resume->uuid}", [], $this->headers());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Resume moved to trash.',
            ]);

        $this->assertSoftDeleted($resume);
    }

    public function test_user_can_add_version(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);

        Storage::fake('local');
        $file = UploadedFile::fake()->create('v2.pdf', 100, 'application/pdf');

        $response = $this->postJson("/api/resumes/{$resume->uuid}/versions", [
            'file' => $file,
        ], $this->headers());

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'New version created.',
            ]);
    }

    public function test_user_can_activate_version(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);
        $version1 = $resume->versions()->create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'version_number' => 1,
            'is_current' => true,
            'original_filename' => 'v1.pdf',
            'file_path' => 'resumes/test/v1.pdf',
            'file_type' => 'pdf',
            'file_size' => 100,
            'parse_status' => 'completed',
        ]);
        $version2 = $resume->versions()->create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'version_number' => 2,
            'is_current' => false,
            'original_filename' => 'v2.pdf',
            'file_path' => 'resumes/test/v2.pdf',
            'file_type' => 'pdf',
            'file_size' => 100,
            'parse_status' => 'completed',
        ]);

        $response = $this->putJson(
            "/api/resumes/{$resume->uuid}/versions/{$version2->uuid}/activate",
            [],
            $this->headers()
        );

        $response->assertStatus(200);

        $this->assertFalse($version1->fresh()->is_current);
        $this->assertTrue($version2->fresh()->is_current);
    }

    public function test_user_can_update_parsed_content(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);
        $version = $resume->versions()->create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'version_number' => 1,
            'is_current' => true,
            'original_filename' => 'resume.pdf',
            'file_path' => 'resumes/test/resume.pdf',
            'file_type' => 'pdf',
            'file_size' => 100,
            'parse_status' => 'pending',
        ]);

        $content = [
            'summary' => 'Experienced software engineer.',
            'skills' => 'PHP, Laravel, React',
            'experience' => 'Senior Developer at Acme Corp',
        ];

        $response = $this->putJson(
            "/api/resumes/{$resume->uuid}/versions/{$version->uuid}/content",
            ['parsed_content' => $content],
            $this->headers()
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('resume_versions', [
            'id' => $version->id,
            'parse_status' => 'completed',
        ]);
    }

    public function test_user_can_list_trash(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);
        $resume->delete();

        $response = $this->getJson('/api/resumes/trash', $this->headers());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_restore_resume(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);
        $resume->delete();

        $response = $this->postJson("/api/resumes/{$resume->uuid}/restore", [], $this->headers());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Resume restored.',
            ]);

        $this->assertNotSoftDeleted($resume);
    }

    public function test_user_can_force_delete_resume(): void
    {
        $resume = Resume::factory()->create(['user_id' => $this->user->id]);
        $resume->delete();

        Storage::fake('local');

        $response = $this->deleteJson("/api/resumes/{$resume->uuid}/force", [], $this->headers());

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Resume permanently deleted.',
            ]);

        $this->assertDatabaseMissing('resumes', ['id' => $resume->id]);
    }
}
