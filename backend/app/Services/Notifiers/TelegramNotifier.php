<?php

declare(strict_types=1);

namespace App\Services\Notifiers;

use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;
use App\Services\ChannelCredentials;
use App\Services\NotificationMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class TelegramNotifier implements Notifier
{
    public function __construct(
        private readonly NotificationMessage $message,
        private readonly ChannelCredentials $credentials,
    ) {
        //
    }

    public function name(): NotificationChannel
    {
        return NotificationChannel::Telegram;
    }

    public function send(User $user, UserChannel $channel, UserJob $userJob): bool
    {
        $credentials = $this->credentials->decrypt($channel);
        $token = (string) ($credentials['bot_token'] ?? config('services.telegram.bot_token') ?? '');
        $chatId = $channel->target ?: (string) (config('services.telegram.chat_id') ?? '');

        if ($token === '' || $chatId === '') {
            Log::warning('Telegram notification skipped: no bot token or chat id', ['user_id' => $user->id]);

            return false;
        }

        $data = $this->message->build($userJob);
        $text = $data['title']."\n\n".$data['body']."\n\n".(string) $userJob->cover_letter;

        $response = Http::timeout((int) config('jobhunter.http.timeout', 12))
            ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

        return $response->successful() && (bool) ($response->json('ok') ?? false);
    }
}
