<?php

namespace App\Http\Requests\Product;

use App\Enums\WeightUnit;
use App\Http\Requests\Crud\DestroyRequest;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Validation\Rule;

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
        /** @var User|null $user */
        $user = $this->user();

        if (!$user || !$user->isAdmin()) {
            return false;
        }

        return $user->restaurant_id === null
            || $user->restaurant_id === ((int) $this->get('restaurant_id'));
    }
}
