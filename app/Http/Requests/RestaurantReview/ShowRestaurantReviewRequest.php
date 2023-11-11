<?php

namespace App\Http\Requests\RestaurantReview;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowRestaurantReviewRequest.
 */
class ShowRestaurantReviewRequest extends ShowRequest
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
