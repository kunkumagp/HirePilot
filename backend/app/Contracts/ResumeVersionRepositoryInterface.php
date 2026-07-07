<?php

namespace App\Contracts;

use App\Models\Resume;
use App\Models\ResumeVersion;

interface ResumeVersionRepositoryInterface
{
    public function findByUuid(string $uuid): ?ResumeVersion;

    public function getByResume(int $resumeId): \Illuminate\Database\Eloquent\Collection;

    public function getCurrentByResume(int $resumeId): ?ResumeVersion;

    public function countByResume(int $resumeId): int;

    public function create(array $data): ResumeVersion;

    public function update(ResumeVersion $version, array $data): bool;

    public function setCurrentVersion(int $resumeId, int $versionId): void;

    public function delete(ResumeVersion $version): bool;
}
