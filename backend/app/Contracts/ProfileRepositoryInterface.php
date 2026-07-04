<?php

namespace App\Contracts;

use App\Models\Profile;
use App\Models\User;

interface ProfileRepositoryInterface
{
    public function findByUser(User $user): ?Profile;

    public function createForUser(User $user, array $data = []): Profile;

    public function update(Profile $profile, array $data): bool;
}
