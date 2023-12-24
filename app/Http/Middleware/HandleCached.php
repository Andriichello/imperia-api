<?php

namespace App\Http\Middleware;

use App\Helpers\CacheHelper;
use App\Helpers\Interfaces\CacheHelperInterface;
use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class HandleCached.
 */
class HandleCached
{
    /**
     * @var CacheHelperInterface
     */
    protected CacheHelperInterface $helper;

    /**
     * HandleCached constructor.
     *
     * @param CacheHelper $helper
     */
    public function __construct(CacheHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Return cached if it's still relevant. Store response if cache had expired.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$groups
     *
     * @return mixed
     * @SuppressWarnings(PHPMD)
     */
    public function handle(Request $request, Closure $next, string ...$groups): mixed
    {
        if (!$this->helper->should($request)) {
            return $next($request);
        }

        $cached = Cache::get($key = $this->helper->key($request, ...$groups));

        if ($cached) {
            $cached = (array) json_decode($cached, true);

            if (isset($cached['data']) && isset($cached['message'])) {
                return ApiResponse::make($cached, 200, $cached['message']);
            }
        }

        $response = $next($request);

        if ($response instanceof JsonResponse && $response->isOk()) {
            $minutes = $this->helper->minutes($request) ?? 5;
            $expiration = now()->addMinutes($minutes);

            Cache::put($key, $response->getContent(), $expiration);
        }

        return $response;
    }
}
