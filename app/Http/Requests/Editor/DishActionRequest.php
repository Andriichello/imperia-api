<?php

namespace App\Http\Requests\Editor;

use App\Models\Dish;

/**
 * Class DishActionRequest.
 *
 * Archive, restore, duplicate or delete a dish.
 */
class DishActionRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<Dish>
     */
    protected function targetClass(): string
    {
        return Dish::class;
    }
}
