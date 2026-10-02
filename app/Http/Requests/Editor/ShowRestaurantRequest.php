<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;

/**
 * Class ShowRestaurantRequest.
 *
 * Everything of the restaurant for the editor.
 */
class ShowRestaurantRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<Restaurant>
     */
    protected function targetClass(): string
    {
        return Restaurant::class;
    }
}
