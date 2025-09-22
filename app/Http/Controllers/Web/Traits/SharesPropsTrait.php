<?php

namespace App\Http\Controllers\Web\Traits;

use App\Helpers\RestaurantHelper;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Menu;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait SharesPropsTrait.
 */
trait SharesPropsTrait
{
    /**
     * Returns shared prop by key.
     *
     * @param Request $request
     * @param string $key One of: 'auth', 'flash', 'locale', 'supported_locales'
     *
     * @return mixed
     */
    public function getSharedProp(Request $request, string $key): mixed
    {
        if ($key === 'auth') {
            return ['user' => $request->user()];
        }

        if ($key === 'flash') {
            return ['message' => $request->session()->get('message')];
        }

        if ($key === 'locale') {
            return config('app.locale');
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
            'auth' => $this->getSharedProp($request, 'auth'),
            'flash' => $this->getSharedProp($request, 'flash'),
            'locale' => $this->getSharedProp($request, 'locale'),
            'supported_locales' => $this->getSharedProp($request, 'supported_locales'),
        ];
    }
}
