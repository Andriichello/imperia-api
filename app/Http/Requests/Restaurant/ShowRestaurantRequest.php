<?php

namespace App\Http\Requests\Restaurant;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowRestaurantRequest.
 */
class ShowRestaurantRequest extends ShowRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                //
            ]
        );
    }
}
