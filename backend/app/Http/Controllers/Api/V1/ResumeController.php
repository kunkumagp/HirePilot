<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Resume\CreateResumeRequest;
use App\Http\Requests\Resume\CreateVersionRequest;
use App\Http\Requests\Resume\UpdateParsedContentRequest;
use App\Http\Requests\Resume\UpdateResumeRequest;
use App\Http\Resources\ResumeResource;
use App\Http\Resources\ResumeVersionResource;
use App\Models\Resume;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function __construct(
        private readonly ResumeService $resumeService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $resumes = $this->resumeService->listResumes($request->user());

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => $resumes,
        ]);
    }

    public function store(CreateResumeRequest $request): JsonResponse
    {
        try {
            $resume = $this->resumeService->createResume(
                $request->user(),
                $request->input('title'),
                $request->file('file')
            );

            return response()->json([
                'success' => true,
                'message' => 'Resume created successfully.',
                'data' => new ResumeResource($resume->load('currentVersion')),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        }
    }

    public function show(Resume $resume): JsonResponse
    {
        $this->authorize('view', $resume);

        $detail = $this->resumeService->getResumeDetail($resume);

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => [
                'resume' => new ResumeResource($detail['resume']),
                'versions' => ResumeVersionResource::collection($detail['versions']),
                'current_version' => $detail['current_version'] ? new ResumeVersionResource($detail['current_version']) : null,
            ],
        ]);
    }

    public function update(UpdateResumeRequest $request, Resume $resume): JsonResponse
    {
        $this->authorize('update', $resume);

        $resume = $this->resumeService->updateResume($resume, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Resume updated.',
            'data' => new ResumeResource($resume),
        ]);
    }

    public function destroy(Resume $resume): JsonResponse
    {
        $this->authorize('delete', $resume);

        $this->resumeService->deleteResume($resume);

        return response()->json([
            'success' => true,
            'message' => 'Resume moved to trash.',
            'data' => null,
        ]);
    }

    public function versions(CreateVersionRequest $request, Resume $resume): JsonResponse
    {
        $this->authorize('view', $resume);

        $resume = $this->resumeService->createVersion($resume, $request->file('file'));

        return response()->json([
            'success' => true,
            'message' => 'New version created.',
            'data' => new ResumeResource($resume->load('currentVersion')),
        ], 201);
    }

    public function activateVersion(Resume $resume, string $version): JsonResponse
    {
        $this->authorize('update', $resume);

        $resume = $this->resumeService->activateVersion($resume, $version);

        return response()->json([
            'success' => true,
            'message' => 'Version activated.',
            'data' => new ResumeResource($resume->load('currentVersion')),
        ]);
    }

    public function updateParsedContent(UpdateParsedContentRequest $request, Resume $resume, string $version): JsonResponse
    {
        $this->authorize('update', $resume);

        $resume = $this->resumeService->updateParsedContent(
            $resume,
            $version,
            $request->input('parsed_content')
        );

        return response()->json([
            'success' => true,
            'message' => 'Parsed content updated.',
            'data' => new ResumeResource($resume->load('currentVersion')),
        ]);
    }

    public function download(Resume $resume, string $version): JsonResponse
    {
        $this->authorize('view', $resume);

        $versionModel = $resume->versions()->where('uuid', $version)->firstOrFail();

        if (!Storage::disk('local')->exists($versionModel->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
                'data' => null,
            ], 404);
        }

        return response()->download(
            Storage::disk('local')->path($versionModel->file_path),
            $versionModel->original_filename
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $resumes = $this->resumeService->getTrashedResumes($request->user());

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => $resumes,
        ]);
    }

    public function restore(string $resume): JsonResponse
    {
        $resumeModel = $this->resumeService->restoreResume($resume);

        if (!$resumeModel) {
            return response()->json([
                'success' => false,
                'message' => 'Resume not found.',
                'data' => null,
            ], 404);
        }

        $this->authorize('update', $resumeModel);

        return response()->json([
            'success' => true,
            'message' => 'Resume restored.',
            'data' => new ResumeResource($resumeModel),
        ]);
    }

    public function forceDelete(string $resume): JsonResponse
    {
        $resumeModel = Resume::withTrashed()->where('uuid', $resume)->first();

        if (!$resumeModel) {
            return response()->json([
                'success' => false,
                'message' => 'Resume not found.',
                'data' => null,
            ], 404);
        }

        $this->authorize('delete', $resumeModel);

        $this->resumeService->forceDeleteResume($resume);

        return response()->json([
            'success' => true,
            'message' => 'Resume permanently deleted.',
            'data' => null,
        ]);
    }
}
