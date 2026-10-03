<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use App\Models\RestaurantReview;
use Illuminate\Validation\Rule;

/**
 * Class IndexReviewsRequest.
 *
 * A page of the restaurant's reviews with a status (the ones, which wait for approval, by default).
 */
class IndexReviewsRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<Restaurant>
     */
    protected function targetClass(): string
    {
        return Restaurant::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::in(RestaurantReview::STATUSES)],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
