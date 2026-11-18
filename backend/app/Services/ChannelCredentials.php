<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserChannel;
use Illuminate\Support\Facades\Crypt;
use Throwable;

final class ChannelCredentials
{
    /**
     * @return array<string, mixed>
     */
    public function decrypt(UserChannel $channel): array
    {
        if ($channel->credentials === null || $channel->credentials === '') {
            return [];
        }

        try {
            $decoded = Crypt::decryptString($channel->credentials);
        } catch (Throwable) {
            return [];
        }

        $values = json_decode($decoded, true);

        return is_array($values) ? $values : [];
    }
}
