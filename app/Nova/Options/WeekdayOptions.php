<?php

namespace App\Nova\Options;

use App\Enums\Weekday;

/**
 * Class WeekdayOptions.
 */
class WeekdayOptions extends Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        $options = [];
        foreach (Weekday::getValues() as $state) {
            $options[$state] = $state;
        }
        return $options;
    }
}
