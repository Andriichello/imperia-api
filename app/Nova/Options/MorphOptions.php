<?php

namespace App\Nova\Options;

use App\Models\Interfaces\CategorizableInterface;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\DiscountableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Providers\MorphServiceProvider;
use Illuminate\Support\Arr;

/**
 * Class MorphOptions.
 */
class MorphOptions extends Options
{
    /**
     * Get map for morph slug to class.
     *
     * @return array
     */
    protected static function getMorphClassToSlugMap(): array
    {
        return array_filter(
            MorphServiceProvider::getMorphMap(),
            fn(string $class) => str_starts_with($class, 'App\\'),
        );
    }

    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        return array_flip(static::getMorphClassToSlugMap());
    }

    /**
     * Get all classes that implement given interfaces.
     *
     * @param string ...$interfaces
     *
     * @return array
     */
    public static function thatImplement(string ...$interfaces): array
    {
        $implementing = array_filter(
            static::getMorphClassToSlugMap(),
            fn(string $class) => Arr::has(class_implements($class), $interfaces),
        );
        return array_flip(MorphServiceProvider::getMorphMap($implementing));
    }

    /**
     * Get options for categorizable classes.
     *
     * @return array
     */
    public static function categorizable(): array
    {
        return static::thatImplement(CategorizableInterface::class);
    }

    /**
     * Get options for commentable classes.
     *
     * @return array
     */
    public static function commentable(): array
    {
        return static::thatImplement(CommentableInterface::class);
    }

    /**
     * Get options for discountable classes.
     *
     * @return array
     */
    public static function discountable(): array
    {
        return static::thatImplement(DiscountableInterface::class);
    }

    /**
     * Get options for loggable classes.
     *
     * @return array
     */
    public static function loggable(): array
    {
        return static::thatImplement(LoggableInterface::class);
    }
}
