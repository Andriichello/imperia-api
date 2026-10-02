<?php

namespace App\Http\Requests\RestaurantReview;

use App\Http\Requests\Crud\StoreRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class StoreRestaurantReviewRequest.
 */
class StoreRestaurantReviewRequest extends StoreRequest
{
    public function messages(): array
    {
        return [
            'ip.unique' => 'You have already left a review for this restaurant.',
        ];
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
                'restaurant_id' => [
                    'required',
                    'integer',
                    'exists:restaurants,id',
                ],
                'ip' => [
                    'required',
                    'string',
                    Rule::unique('restaurant_reviews', 'ip')
                        ->where('restaurant_id', $this->get('restaurant_id'))
                ],
                'reviewer' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                ],
                'score' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:5',
                ],
                'title' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:2',
                    'max:255',
                ],
                'description' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:2',
                    'max:255',
                ],
            ]
        );
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->mergeIfMissing(['ip' => Request::ip()]);
    }

    /**
     * @OA\Schema(
     *   schema="StoreRestaurantReviewRequest",
     *   description="Store restaurant review request",
     *   required={"restaurant_id", "ip", "reviewer", "score"},
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="ip", type="string", example="127.0.0.1"),
     *   @OA\Property(property="reviewer", type="string", example="Steve"),
     *   @OA\Property(property="score", type="integer", example=5,
     *     description="Min value: `0`, max value: `5`."),
     *   @OA\Property(property="title", type="string", nullable=true, example="Great"),
     *   @OA\Property(property="description", type="string", nullable=true,
     *     example="This is a great establishment, nice personnel."),
     *  )
     */
}
