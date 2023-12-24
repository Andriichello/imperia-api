<?php

namespace App\Helpers\Interfaces;

use Illuminate\Http\Request;

/**
 * Interface CacheHelperInterface.
 */
interface CacheHelperInterface
{
    /**
     * Get cache settings for the given path.
     *
     * @param string $path
     *
     * @return array|null
     */
    public function settings(string $path): ?array;

    /**
     * Get the number of minutes that the response should
     * be cached for the given path.
     *
     * @param string $path
     *
     * @return int|null
     */
    public function minutes(string $path): ?int;

    /**
     * Get the groups that response should be cached
     * into with the given path.
     *
     * @param string $path
     *
     * @return array|null
     */
    public function groups(string $path): ?array;

    /**
     * Determines if request's response should be cached.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function should(Request $request): bool;

    /**
     * Get request's unique key for caching.
     *
     * @param Request $request
     * @param string ...$groups
     *
     * @return string
     */
    public function key(Request $request, string ...$groups): string;

    /**
     * Get request's prefix for caching.
     *
     * @param Request $request
     * @param string ...$groups
     *
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function group(Request $request, string ...$groups): string;

    /**
     * Get request's path for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function path(Request $request): string;

    /**
     * Get request's payload hash for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function hash(Request $request): string;
}
