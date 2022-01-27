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
     * @return string
     */
    function usesTrait(string|object $class, string $trait): string
    {
        return in_array($trait, class_uses_recursive($class), true);
    }
}
