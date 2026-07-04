<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function findByUuid(string $uuid): ?User;

    public function findById(int $id): ?User;

    public function create(array $data): User;

    public function update(User $user, array $data): bool;

    public function delete(User $user): bool;

    public function getAllActive(): Collection;
}
