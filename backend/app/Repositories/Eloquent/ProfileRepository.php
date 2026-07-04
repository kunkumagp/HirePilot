<?php

namespace App\Repositories\Eloquent;

use App\Contracts\ProfileRepositoryInterface;
use App\Models\Profile;
use App\Models\User;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function findByUser(User $user): ?Profile
    {
        return Profile::where('user_id', $user->id)->first();
    }

    public function createForUser(User $user, array $data = []): Profile
    {
        return $user->profile()->create($data);
    }

    public function update(Profile $profile, array $data): bool
    {
        return $profile->update($data);
    }
}
