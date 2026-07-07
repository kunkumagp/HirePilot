<?php

namespace App\Repositories\Eloquent;

use App\Contracts\ResumeVersionRepositoryInterface;
use App\Models\Resume;
use App\Models\ResumeVersion;
use Illuminate\Database\Eloquent\Collection;

class ResumeVersionRepository implements ResumeVersionRepositoryInterface
{
    public function findByUuid(string $uuid): ?ResumeVersion
    {
        return ResumeVersion::where('uuid', $uuid)->first();
    }

    public function getByResume(int $resumeId): Collection
    {
        return ResumeVersion::where('resume_id', $resumeId)
            ->orderBy('version_number', 'desc')
            ->get();
    }

    public function getCurrentByResume(int $resumeId): ?ResumeVersion
    {
        return ResumeVersion::where('resume_id', $resumeId)
            ->where('is_current', true)
            ->first();
    }

    public function countByResume(int $resumeId): int
    {
        return ResumeVersion::where('resume_id', $resumeId)->count();
    }

    public function create(array $data): ResumeVersion
    {
        return ResumeVersion::create($data);
    }

    public function update(ResumeVersion $version, array $data): bool
    {
        return $version->update($data);
    }

    public function setCurrentVersion(int $resumeId, int $versionId): void
    {
        ResumeVersion::where('resume_id', $resumeId)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        ResumeVersion::where('id', $versionId)
            ->where('resume_id', $resumeId)
            ->update(['is_current' => true]);
    }

    public function delete(ResumeVersion $version): bool
    {
        return $version->delete();
    }
}
