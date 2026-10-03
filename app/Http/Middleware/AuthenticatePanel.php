<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate;

/**
 * Class AuthenticatePanel.
 *
 * The admin panel has no sign-in page of its own: guests sign in at the admin's one,
 * which brings them back (the session is shared).
 */
class AuthenticatePanel extends Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param mixed $request
     *
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function redirectTo($request): ?string
    {
        return route('admin.login');
    }
}
