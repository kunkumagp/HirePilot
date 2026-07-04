<?php

namespace App\Services;

use App\Contracts\ProfileRepositoryInterface;
use App\Models\User;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepositoryInterface $profileRepository,
    ) {}

    public function getProfile(User $user): array
    {
        $profile = $this->profileRepository->findByUser($user);

        return [
            'user' => $user,
            'profile' => $profile,
        ];
    }

    public function updateProfile(User $user, array $data): array
    {
        $profile = $this->profileRepository->findByUser($user);

        $userData = array_intersect_key($data, array_flip(['name', 'timezone']));
        if (!empty($userData)) {
            $user->update($userData);
        }

        $profileData = array_intersect_key($data, array_flip([
            'phone', 'headline', 'bio', 'location',
            'linkedin_url', 'github_url', 'website_url', 'preferences',
        ]));

        if (!empty($profileData) && $profile) {
            $this->profileRepository->update($profile, $profileData);
        }

        return [
            'user' => $user->fresh(),
            'profile' => $profile?->fresh(),
        ];
    }
}
