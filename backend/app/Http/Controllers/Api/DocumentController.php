<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadDocumentRequest;
use App\Models\Document;
use App\Models\User;
use App\Services\AtsCvGenerator;
use App\Services\CvDataExtractor;
use App\Services\PdfTextExtractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly PdfTextExtractor $pdfText,
        private readonly CvDataExtractor $cvData,
        private readonly AtsCvGenerator $cvGenerator,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(
            $user->documents()->latest()->get()->map(fn (Document $document): array => [
                'id' => $document->id,
                'original_name' => $document->original_name,
                'size' => $document->size,
                'extracted_data' => $document->extracted_data,
                'created_at' => $document->created_at?->toIso8601String(),
            ])->all(),
        );
    }

    public function store(UploadDocumentRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $file = $request->file('file');

        $filename = Str::random(20).'.pdf';
        $path = $file->storeAs('private/documents/'.$user->id, $filename, 'local');

        $text = $this->pdfText->extract(Storage::disk('local')->path($path));
        $extracted = $this->cvData->extract($text);

        $document = $user->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime' => 'application/pdf',
            'size' => $file->getSize(),
            'parsed_text' => $text,
            'extracted_data' => $extracted,
        ]);

        return response()->json([
            'id' => $document->id,
            'original_name' => $document->original_name,
            'size' => $document->size,
            'extracted_data' => $document->extracted_data,
            'created_at' => $document->created_at?->toIso8601String(),
        ], 201);
    }

    public function destroy(Request $request, Document $document): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($document->user_id !== $user->id) {
            abort(404);
        }

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return response()->noContent();
    }

    public function generate(Request $request, Document $document, string $format): BinaryFileResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($document->user_id !== $user->id) {
            abort(404);
        }

        if (! in_array($format, ['docx', 'pdf'], true)) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'email' => ['sometimes', 'nullable', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:60'],
            'summary' => ['sometimes', 'nullable', 'string'],
            'skills' => ['sometimes', 'array'],
            'experience' => ['sometimes', 'nullable', 'string'],
            'education' => ['sometimes', 'nullable', 'string'],
            'projects' => ['sometimes', 'nullable', 'string'],
            'certifications' => ['sometimes', 'nullable', 'string'],
            'languages' => ['sometimes', 'nullable', 'string'],
            'awards' => ['sometimes', 'nullable', 'string'],
        ]);

        $data = array_merge($document->extracted_data ?? [], $validated);
        $baseName = 'ats-cv-'.Str::slug((string) ($data['name'] ?? 'candidate'));

        $path = $format === 'docx'
            ? $this->cvGenerator->toDocx($data)
            : $this->cvGenerator->toPdf($data);

        return response()
            ->download($path, $baseName.'.'.$format)
            ->deleteFileAfterSend(true);
    }
}
