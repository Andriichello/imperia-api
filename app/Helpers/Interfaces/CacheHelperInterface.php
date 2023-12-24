<?php

namespace App\Helpers\Interfaces;

use Illuminate\Http\Request;

/**
 * Interface CacheHelperInterface.
 */
interface CacheHelperInterface
{
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
     *
     * @return string
     */
    public function key(Request $request): string;

    /**
     * Get request's prefix for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function prefix(Request $request): string;

    /**
     * Get request's payload hash for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function hash(Request $request): string;
}
