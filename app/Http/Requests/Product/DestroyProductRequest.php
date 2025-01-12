<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\Crud\DestroyRequest;
use App\Models\Product;
use App\Rules\SameRestaurant;

/**
 * Class DestroyProductRequest.
 */
class DestroyProductRequest extends DestroyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return SameRestaurant::make($this->user(), Product::class)
            ->onlyAdmins()
            ->validate('id', $this->id(), fn() => null);
    }
}
