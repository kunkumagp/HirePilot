<?php

declare(strict_types=1);

namespace App;

enum NotificationChannel: string
{
    case Telegram = 'telegram';
    case Email = 'email';
    case InApp = 'inapp';
}
