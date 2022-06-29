<?php

use Illuminate\Support\Str;

if (!function_exists('labelize')) {

    /**
     * Splits column name into words and capitalizes first letter in each then translates it.
     *
     * @param string $column
     * @return string
     */
    function labelize(string $column): string
    {
        return Str::of($column)
            ->explode('.')
            ->map(function ($string) {
                $string = str_replace(['.', ',', '_', '-'], ' ', $string);
                return trans(ucwords($string));
            })->implode('.');
    }
}

if (!function_exists('usesTrait')) {

    /**
     * Determines whether class or object uses a trait.
     *
     * @param string|object $class
     * @param string $trait
     *
     * @return bool
     */
    function usesTrait(string|object $class, string $trait): bool
    {
        return in_array($trait, class_uses_recursive($class), true);
    }
}

if (!function_exists('slugClass')) {

    /**
     * Get class name as slug.
     *
     * @param string|object $class
     *
     * @return string
     */
    function slugClass(string|object $class): string
    {
        $name = Str::of(is_object($class) ? get_class($class) : $class)
            ->explode('\\')->last();
        $words = preg_split('/(?=[A-Z])/', $name);
        $slug = collect($words)->filter()->implode('-');
        return Str::plural(strtolower($slug));
    }
}

if (!function_exists('splitName')) {

    /**
     * Get first and last name from the full name
     *
     * @param string $name
     *
     * @return array
     */
    function splitName(string $name): array
    {
        $name = trim($name);

        $lastName = !str_contains($name, ' ') ? null : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $firstName = trim(preg_replace('#' . preg_quote($lastName, '#') . '#', '', $name));

        return [$firstName, $lastName];
    }
}

if (!function_exists('extractValues')) {

    /**
     * Extract column from given items.
     *
     * @param string $key
     * @param mixed ...$items
     *
     * @return array
     */
    function extractValues(string $key, mixed ...$items): array
    {
        $closure = function (mixed $item) use ($key) {
            if (is_array($item) || is_object($item)) {
                return data_get($item, $key);
            }

            return $item;
        };

        return array_map($closure, $items);
    }
}
