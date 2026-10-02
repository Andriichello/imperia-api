<?php

namespace App\Http\Controllers\Web\Traits;

use Illuminate\Http\Request;

/**
 * Trait SharesPropsTrait.
 */
trait SharesPropsTrait
{
    /**
     * Returns shared prop by key.
     *
     * @param Request $request
     * @param string $key One of: 'locale', 'supported_locales'
     *
     * @return mixed
     */
    public function getSharedProp(Request $request, string $key): mixed
    {
        if ($key === 'locale') {
            return $request->route('locale') ?? config('app.locale');
        }

        if ($key === 'supported_locales') {
            return config('app.supported_locales');
        }

        return null;
    }

    /**
     * Returns shared props.
     *
     * @param Request $request
     *
     * @return array
     */
    public function getSharedProps(Request $request): array
    {
        return [
            'locale' => $this->getSharedProp($request, 'locale'),
            'supported_locales' => $this->getSharedProp($request, 'supported_locales'),
        ];
    }
}
