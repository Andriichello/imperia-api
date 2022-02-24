<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowDiscountRequest.
 */
class ShowDiscountRequest extends ShowRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                //
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
