<?php

namespace App\Nova\Options;

/**
 * Class Options.
 */
abstract class Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public abstract static function all(): array;
}
