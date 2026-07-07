<?php

namespace App\Repositories\Eloquent;

use App\Contracts\ResumeRepositoryInterface;
use App\Models\Resume;
use Illuminate\Database\Eloquent\Collection;

class ResumeRepository implements ResumeRepositoryInterface
{
    public function findByUuid(string $uuid): ?Resume
    {
        return Resume::where('uuid', $uuid)->first();
    }

    public function findById(int $id): ?Resume
    {
        return Resume::find($id);
    }

    public function getByUser(int $userId, array $options = []): Collection
    {
        $query = Resume::where('user_id', $userId);

        if (($options['include_trashed'] ?? false)) {
            $query->withTrashed();
        }

        if (isset($options['is_active'])) {
            $query->where('is_active', $options['is_active']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getTrashedByUser(int $userId): Collection
    {
        return Resume::where('user_id', $userId)
            ->onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();
    }

    public function countByUser(int $userId): int
    {
        return Resume::where('user_id', $userId)->count();
    }

    public function create(array $data): Resume
    {
        return Resume::create($data);
    }

    public function update(Resume $resume, array $data): bool
    {
        return $resume->update($data);
    }

    public function delete(Resume $resume): bool
    {
        return $resume->delete();
    }

    public function restore(Resume $resume): bool
    {
        return $resume->restore();
    }

    public function forceDelete(Resume $resume): bool
    {
        return $resume->forceDelete();
    }
}
