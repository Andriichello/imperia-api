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

        return false;
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
