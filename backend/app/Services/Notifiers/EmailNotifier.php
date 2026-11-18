<?php

declare(strict_types=1);

namespace App\Services\Notifiers;

use App\Mail\JobMatchMail;
use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserJob;
use App\NotificationChannel;
use App\Services\ChannelCredentials;
use App\Services\NotificationMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

final class EmailNotifier implements Notifier
{
    public function __construct(
        private readonly NotificationMessage $message,
        private readonly ChannelCredentials $credentials,
    ) {
        //
    }

    public function name(): NotificationChannel
    {
        return NotificationChannel::Email;
    }

    public function send(User $user, UserChannel $channel, UserJob $userJob): bool
    {
        $credentials = $this->credentials->decrypt($channel);
        $recipient = $channel->target ?: $user->email;

        if ($recipient === '') {
            return false;
        }

        $data = $this->message->build($userJob);
        $mail = new JobMatchMail(
            jobSubject: '🔥 '.$data['title'],
            messageBody: $data['body'],
            coverLetterText: (string) $userJob->cover_letter,
        );

        if ($credentials !== []) {
            Config::set('mail.mailers.smtp.host', $credentials['smtp_host'] ?? config('mail.mailers.smtp.host'));
            Config::set('mail.mailers.smtp.port', (int) ($credentials['smtp_port'] ?? config('mail.mailers.smtp.port', 587)));
            Config::set('mail.mailers.smtp.encryption', $credentials['smtp_encryption'] ?? config('mail.mailers.smtp.encryption', 'tls'));
            Config::set('mail.mailers.smtp.username', $credentials['smtp_username'] ?? null);
            Config::set('mail.mailers.smtp.password', $credentials['smtp_password'] ?? null);
            Config::set('mail.from.address', $credentials['from_address'] ?? config('mail.from.address'));

            Mail::mailer('smtp')->to($recipient)->send($mail);
        } else {
            Mail::to($recipient)->send($mail);
        }

        return true;
    }
}
