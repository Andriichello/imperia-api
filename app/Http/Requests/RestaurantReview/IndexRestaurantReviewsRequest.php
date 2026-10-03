<?php

namespace App\Http\Requests\RestaurantReview;

use App\Queries\RestaurantReviewQueryBuilder;
use Illuminate\Validation\Rule;

/**
 * Class IndexRestaurantReviewsRequest.
 *
 * A page of the restaurant's public reviews.
 */
class IndexRestaurantReviewsRequest extends RestaurantReviewRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'sort' => ['sometimes', Rule::in(RestaurantReviewQueryBuilder::SORTS)],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
