<?php

namespace App\Enums;

/**
 * Enum NotificationChannel.
 *
 * @method static NotificationChannel Default()
 * @method static NotificationChannel Email()
 * @method static NotificationChannel Sms()
 *
 * @SuppressWarnings(PHPMD)
 */
class NotificationChannel extends Enum
{
    public const Default = 'default';
    public const Email = 'email';
    public const Sms = 'sms';
}
