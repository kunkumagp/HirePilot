<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Document;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    private function pdfContent(): string
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml('<h1>Jane Doe</h1><p>jane@example.com</p><h2>SUMMARY</h2><p>React developer with 5 years experience.</p><h2>SKILLS</h2><p>React, PHP, Laravel</p><h2>EDUCATION</h2><p>BSc Computer Science</p>');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function test_user_can_upload_cv_and_data_is_extracted(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $response = $this->withToken($user->createToken('spa')->plainTextToken)
            ->post('/api/documents', [
                'file' => UploadedFile::fake()->createWithContent('jane-cv.pdf', $this->pdfContent()),
            ]);

        $response->assertCreated()->assertJsonPath('original_name', 'jane-cv.pdf');

        $document = $user->documents()->firstOrFail();
        $this->assertStringContainsString('React', $document->parsed_text);
        $this->assertContains('React', $document->extracted_data['skills']);
        $this->assertSame('jane@example.com', $document->extracted_data['email']);
    }

    public function test_user_can_generate_docx_and_pdf_from_uploaded_cv(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $document = $user->documents()->create([
            'original_name' => 'jane-cv.pdf',
            'path' => 'private/documents/'.$user->id.'/fake.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
            'extracted_data' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'skills' => ['React', 'PHP'],
                'summary' => 'React developer.',
            ],
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        foreach (['docx', 'pdf'] as $format) {
            $response = $this->withToken($token)
                ->postJson('/api/documents/'.$document->id.'/cv/'.$format, [
                    'name' => 'Jane Doe',
                    'skills' => ['React', 'PHP'],
                ]);

            $response->assertOk();
            $this->assertStringContainsString(
                'ats-cv',
                (string) $response->headers->get('Content-Disposition'),
            );
        }
    }

    public function test_user_cannot_delete_another_users_document(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $document = $owner->documents()->create([
            'original_name' => 'cv.pdf',
            'path' => 'private/documents/'.$owner->id.'/fake.pdf',
            'mime' => 'application/pdf',
            'size' => 1024,
        ]);

        $this->withToken($other->createToken('spa')->plainTextToken)
            ->deleteJson('/api/documents/'.$document->id)
            ->assertNotFound();

        $this->assertNotNull(Document::find($document->id));
    }
}
