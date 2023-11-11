<?php

namespace App\Nova\Options;

use App\Enums\WeightUnit;

/**
 * Class WeightUnitOptions.
 */
class WeightUnitOptions extends Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        $options = [];
        foreach (WeightUnit::getValues() as $state) {
            $options[$state] = $state;
        }
        return $options;
    }
}
