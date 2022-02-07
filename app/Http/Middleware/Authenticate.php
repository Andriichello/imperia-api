<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Class Authenticate.
 */
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @throws AuthenticationException
     */
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson()) {
            throw new AuthenticationException();
        }
        return route('login');
    }
}
