<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowOrderRequest.
 */
class ShowOrderRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                'comments',
                'spaces.comments',
                'tickets.comments',
                'products.comments',
                'services.comments',
            ]
        );
    }

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
