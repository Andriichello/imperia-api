<?php

namespace App\Http\Requests\Editor;

use App\Models\RestaurantReview;

/**
 * Class ReviewActionRequest.
 *
 * Approve or reject a review of the restaurant.
 */
class ReviewActionRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<RestaurantReview>
     */
    protected function targetClass(): string
    {
        return RestaurantReview::class;
    }
}
