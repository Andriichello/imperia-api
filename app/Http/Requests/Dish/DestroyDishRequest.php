<?php

namespace App\Http\Requests\Dish;

use App\Http\Requests\Crud\DestroyRequest;
use App\Models\Dish;
use App\Rules\SameRestaurant;

/**
 * Class DestroyDishRequest.
 */
class DestroyDishRequest extends DestroyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return SameRestaurant::make($this->user(), Dish::class)
            ->onlyAdmins()
            ->validate('id', $this->id(), fn() => null);
    }
}
