<?php

namespace App\Helpers;

use App\Helpers\Interfaces\CacheHelperInterface;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Requests\CrudRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        if ($request instanceof CrudRequest) {
            if ($request->getMethod() !== 'GET') {
                return false;
            }

            $isIndex = $request instanceof IndexRequest;
            $isShow = $request instanceof ShowRequest;

            if ($isIndex || $isShow) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get request's unique key for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function key(Request $request): string
    {
        return "{$this->prefix($request)}:{$this->hash($request)}";
    }

    /**
     * Get request's prefix for caching.
     *
     * @param Request $request
     *
     * @return string
     */
    public function prefix(Request $request): string
    {
        return "[{$request->path()}]";
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
        return Hash::make(json_encode($request->all()));
    }
}
