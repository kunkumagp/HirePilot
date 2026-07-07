<?php

namespace App\Services;

use App\Contracts\ResumeRepositoryInterface;
use App\Contracts\ResumeVersionRepositoryInterface;
use App\Jobs\ParseResumeJob;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ResumeService
{
    private const MAX_RESUMES_PER_USER = 5;

    public function __construct(
        private readonly ResumeRepositoryInterface $resumeRepository,
        private readonly ResumeVersionRepositoryInterface $versionRepository,
    ) {}

    public function listResumes(User $user): array
    {
        $resumes = $this->resumeRepository->getByUser($user->id, ['is_active' => true]);

        return $resumes->map(fn (Resume $resume) => [
            'resume' => $resume,
            'current_version' => $this->versionRepository->getCurrentByResume($resume->id),
            'version_count' => $this->versionRepository->countByResume($resume->id),
        ])->toArray();
    }

    public function createResume(User $user, string $title, UploadedFile $file): Resume
    {
        $count = $this->resumeRepository->countByUser($user->id);
        if ($count >= self::MAX_RESUMES_PER_USER) {
            throw new \RuntimeException('Maximum of ' . self::MAX_RESUMES_PER_USER . ' resumes reached.');
        }

        $resume = $this->resumeRepository->create([
            'user_id' => $user->id,
            'title' => $title,
        ]);

        $this->createVersion($resume, $file);

        return $resume;
    }

    public function createVersion(Resume $resume, UploadedFile $file): Resume
    {
        $versionCount = $this->versionRepository->countByResume($resume->id);
        $versionNumber = $versionCount + 1;

        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs(
            "resumes/{$resume->user_id}/{$resume->uuid}",
            "v{$versionNumber}.{$extension}",
            'local'
        );

        $version = $this->versionRepository->create([
            'resume_id' => $resume->id,
            'version_number' => $versionNumber,
            'is_current' => $versionCount === 0,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $file->getSize(),
            'parse_status' => 'pending',
        ]);

        ParseResumeJob::dispatch($version);

        return $resume->fresh();
    }

    public function getResumeDetail(Resume $resume): array
    {
        $versions = $this->versionRepository->getByResume($resume->id);
        $currentVersion = $this->versionRepository->getCurrentByResume($resume->id);

        return [
            'resume' => $resume,
            'versions' => $versions,
            'current_version' => $currentVersion,
        ];
    }

    public function updateResume(Resume $resume, array $data): Resume
    {
        $this->resumeRepository->update($resume, $data);
        return $resume->fresh();
    }

    public function deleteResume(Resume $resume): void
    {
        $this->resumeRepository->delete($resume);
    }

    public function restoreResume(string $uuid): ?Resume
    {
        $resume = Resume::withTrashed()->where('uuid', $uuid)->first();
        if ($resume) {
            $this->resumeRepository->restore($resume);
        }
        return $resume;
    }

    public function forceDeleteResume(string $uuid): ?Resume
    {
        $resume = Resume::withTrashed()->where('uuid', $uuid)->first();
        if ($resume) {
            Storage::disk('local')->deleteDirectory("resumes/{$resume->user_id}/{$resume->uuid}");
            $this->resumeRepository->forceDelete($resume);
        }
        return $resume;
    }

    public function getTrashedResumes(User $user): array
    {
        return $this->resumeRepository->getTrashedByUser($user->id)->toArray();
    }

    public function activateVersion(Resume $resume, string $versionUuid): Resume
    {
        $version = $this->versionRepository->findByUuid($versionUuid);
        if (!$version || $version->resume_id !== $resume->id) {
            throw new \RuntimeException('Version not found.');
        }

        $this->versionRepository->setCurrentVersion($resume->id, $version->id);
        return $resume->fresh();
    }

    public function updateParsedContent(Resume $resume, string $versionUuid, array $parsedContent): Resume
    {
        $version = $this->versionRepository->findByUuid($versionUuid);
        if (!$version || $version->resume_id !== $resume->id) {
            throw new \RuntimeException('Version not found.');
        }

        $this->versionRepository->update($version, [
            'parsed_content' => $parsedContent,
            'parse_status' => 'completed',
        ]);

        return $resume->fresh();
    }
}
