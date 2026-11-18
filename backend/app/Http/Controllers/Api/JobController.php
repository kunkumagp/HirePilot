<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JobController extends Controller
{
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
}
