<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobDetailResource;
use App\Http\Resources\JobResource;
use App\Models\User;
use App\Models\UserJob;
use App\Services\CoverLetterService;
use App\Services\JobDataFactory;
use App\Services\JobRunService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobController extends Controller
{
    public function __construct(
        private readonly CoverLetterService $coverLetters,
        private readonly JobDataFactory $jobDataFactory,
        private readonly JobRunService $runService,
    ) {
        //
    }

    public function runSearch(): JsonResponse
    {
        return response()->json($this->runService->run(true));
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $jobs = $user->userJobs()
            ->with('jobPosting')
            ->orderByDesc('score')
            ->orderByDesc('id')
            ->paginate(20);

        return JobResource::collection($jobs);
    }

    public function show(Request $request, UserJob $job): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            abort(404);
        }

        $job->load('jobPosting');

        return response()->json(new JobDetailResource($job));
    }

    public function regenerateCoverLetter(Request $request, UserJob $job): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($job->user_id !== $user->id) {
            abort(404);
        }

        $job->load('jobPosting');
        $jobData = $this->jobDataFactory->fromPosting($job->jobPosting);
        $letter = $this->coverLetters->generate($user, $jobData);

        $job->forceFill(['cover_letter' => $letter])->save();

        return response()->json(['cover_letter' => $letter]);
    }
}
