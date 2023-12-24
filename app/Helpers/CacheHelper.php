<?php

namespace App\Helpers;

use App\Helpers\Interfaces\CacheHelperInterface;
use Illuminate\Http\Request;

/**
 * Class CacheHelper.
 */
class CacheHelper implements CacheHelperInterface
{
    /**
     * Paths that should be cached if possible
     * and their expiration duration (minutes).
     *
     * @var array
     */
    public static array $caching = [
        'api/restaurants' =>  [
            'minutes' => 120,
            'groups' => ['restaurants'],
        ],
        'api/restaurant-reviews'  => [
            'minutes' => 120,
            'groups' => ['restaurants'],
        ],
        'api/menus' => [
            'minutes' => 120,
            'groups' => ['menus', 'products', 'categories', 'alterations'],
        ],
        'api/products' => [
            'minutes' => 5,
            'groups' => ['menus', 'products', 'categories', 'alterations'],
        ],
        'api/tickets' => [
            'minutes' => 120,
            'groups' => ['tickets', 'categories'],
        ],
        'api/services' => [
            'minutes' => 120,
            'groups' => ['services', 'categories'],
        ],
        'api/spaces' => [
            'minutes' => 120,
            'groups' => ['spaces', 'categories'],
        ],
        'api/customers' => [
            'minutes' => 5,
            'groups' => ['customers'],
        ],
    ];

    /**
     * Get cache settings for the given path.
     *
     * @param string $path
     *
     * @return array|null
     */
    public function settings(string $path): ?array
    {
        foreach (static::$caching as $prefix => $settings) {
            if (str_starts_with($path, $prefix)) {
                return $settings;
            }
        }

        return null;
    }

    /**
     * Get the number of minutes that the response should
     * be cached for the given path.
     *
     * @param string $path
     *
     * @return int|null
     */
    public function minutes(string $path): ?int
    {
        return data_get($this->settings($path), 'minutes');
    }

    /**
     * Get the groups that response should be cached
     * into with the given path.
     *
     * @param string $path
     *
     * @return array|null
     */
    public function groups(string $path): ?array
    {
        return data_get($this->settings($path), 'groups');
    }

    /**
     * Determines if request's response should be cached.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function should(Request $request): bool
    {
        if ($request->getMethod() === 'GET') {
            return true;
        }

        return (bool) $this->minutes($request);
    }

    /**
     * Get request's unique key for caching.
     *
     * @param Request $request
     * @param string ...$groups
     *
     * @return string
     */
    public function key(Request $request, string ...$groups): string
    {
        return sprintf(
            '[%s]:<%s>:{%s}',
            $this->group($request, ...$groups),
            $this->path($request),
            $this->hash($request)
        );
    }

    /**
     * Get request's prefix for caching.
     *
     * @param Request $request
     * @param string ...$groups
     *
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function group(Request $request, string ...$groups): string
    {
        return implode(',', $groups);
    }

    /**
     * Get request's path for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function path(Request $request): string
    {
        return $request->path();
    }

    /**
     * Get request's payload hash for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function hash(Request $request): string
    {
        return hash('md5', json_encode($request->all()));
    }
}
