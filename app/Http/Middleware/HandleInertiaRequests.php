<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Class HandleInertiaRequests.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn() => $request->session()->get('message')
            ],
            'locale' => config('app.locale'),
            'supported_locales' => config('app.supported_locales'),
        ]);
    }

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @param Request $request
     *
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function version(Request $request): ?string
    {
        return env('NEW_UI_VERSION') ?? now()->format('d.m.Y H:00');
    }
}
