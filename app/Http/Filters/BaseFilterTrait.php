<?php

namespace App\Http\Filters;

/**
 * Trait BaseFilter.
 *
 * @package App\Http\Filters
 */
trait BaseFilterTrait
{
    /**
     * Extract ids from given value.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function extract(mixed $value): array
    {
        if (is_int($value)) {
            return [$value];
        }

        if (is_string($value)) {
            return array_map(
                fn ($val) => (int) $val,
                explode(',', $value)
            );
        }

        if (is_array($value)) {
            return array_map(
                fn ($val) => (int) $val,
                $value
            );
        }

        return [];
    }
}
