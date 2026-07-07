<?php

namespace App\Contracts;

use App\Models\Resume;
use Illuminate\Database\Eloquent\Collection;

interface ResumeRepositoryInterface
{
    public function findByUuid(string $uuid): ?Resume;

    public function findById(int $id): ?Resume;

    public function getByUser(int $userId, array $options = []): Collection;

    public function getTrashedByUser(int $userId): Collection;

    public function countByUser(int $userId): int;

    public function create(array $data): Resume;

    public function update(Resume $resume, array $data): bool;

    public function delete(Resume $resume): bool;

    public function restore(Resume $resume): bool;

    public function forceDelete(Resume $resume): bool;
}
