<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowOrderRequest.
 */
class ShowOrderRequest extends ShowRequest
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
