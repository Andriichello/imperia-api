<?php

namespace App\Helpers;

/**
 * Class ColorHelper.
 *
 * Contrast of colors, as WCAG defines it (from 1:1 to 21:1).
 */
class ColorHelper
{
    /**
     * Tints of the brand color, which the public pages put text on (over white).
     *
     * @var float[]
     */
    public const TINTS = [0.1, 0.15, 0.2];

    /**
     * Contrast, which text needs to be readable (WCAG AA).
     *
     * @var float
     */
    public const READABLE = 4.5;

    /**
     * Whether the value is a hex color, e.g. `#3bb517`.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isHex(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /**
     * Red, green and blue of a hex color, from 0 to 255.
     *
     * @param string $hex
     *
     * @return int[]
     */
    public static function toRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * The color mixed into another one (white by default), e.g. a 20 % tint.
     *
     * @param string $color
     * @param float $amount of the color, from 0 to 1
     * @param string $base
     *
     * @return float[]
     */
    public static function tint(string $color, float $amount, string $base = '#ffffff'): array
    {
        $color = static::toRgb($color);
        $base = static::toRgb($base);

        return array_map(
            fn (int $channel, int $baseChannel) => $baseChannel + ($channel - $baseChannel) * $amount,
            $color,
            $base
        );
    }

    /**
     * Relative luminance of a color, from 0 (black) to 1 (white).
     *
     * @param int[]|float[] $rgb
     *
     * @return float
     */
    public static function luminance(array $rgb): float
    {
        $linear = array_map(function (int|float $channel) {
            $value = $channel / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
    }

    /**
     * Contrast of two colors, from 1 to 21.
     *
     * @param int[]|float[] $first
     * @param int[]|float[] $second
     *
     * @return float
     */
    public static function contrast(array $first, array $second): float
    {
        $lighter = max(static::luminance($first), static::luminance($second));
        $darker = min(static::luminance($first), static::luminance($second));

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Contrast of the text color on the brand color's tints, which the public pages
     * use (the lowest of them, i.e. on the strongest tint).
     *
     * @param string $primary
     * @param string $text
     *
     * @return float
     */
    public static function contrastOnTints(string $primary, string $text): float
    {
        $contrasts = array_map(
            fn (float $amount) => static::contrast(static::tint($primary, $amount), static::toRgb($text)),
            static::TINTS
        );

        return min($contrasts);
    }
}
