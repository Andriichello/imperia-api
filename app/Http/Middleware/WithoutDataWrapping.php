<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WithoutDataWrapping.
 */
class WithoutDataWrapping
{
    /**
     * Changes app's locale if the corresponding header was specified.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        JsonResource::withoutWrapping();
        // continue as if nothing happened
        return $next($request);
    }
}
