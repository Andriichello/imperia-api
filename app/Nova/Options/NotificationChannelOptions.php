<?php

namespace App\Nova\Options;

use App\Enums\NotificationChannel;

/**
 * Class NotificationChannelOptions.
 */
class NotificationChannelOptions extends Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        $options = [];
        foreach (NotificationChannel::getValues() as $state) {
            $options[$state] = $state;
        }
        return $options;
    }
}
