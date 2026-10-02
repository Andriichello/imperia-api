<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class UsePanelAuthGuard.
 *
 * Makes the admin panel's guard (`web`) the default one for the whole request.
 * The app's default guard is `sanctum`, which `AuthenticateSession` can't work
 * with. Filament's `Authenticate` switches the guard too, but it doesn't run on
 * the login page, so a logged-in user opening it got an error.
 */
class UsePanelAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        Auth::shouldUse(Filament::getAuthGuard());

        return $next($request);
    }
}
