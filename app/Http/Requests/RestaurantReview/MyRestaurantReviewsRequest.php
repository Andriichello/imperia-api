<?php

namespace App\Http\Requests\RestaurantReview;

use OpenApi\Annotations as OA;

/**
 * Class MyRestaurantReviewsRequest.
 *
 * The reviews a device left (by their ids and the device's token), whatever their status.
 */
class MyRestaurantReviewsRequest extends RestaurantReviewRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'client_token' => ['required', 'string', 'min:16', 'max:100'],
            'ids' => ['required', 'array', 'max:50'],
            'ids.*' => ['integer'],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="MyRestaurantReviewsRequest",
     *   description="Reviews the device left: their ids and the device's token.",
     *   required={"client_token", "ids"},
     *   @OA\Property(property="client_token", type="string", example="0f8c5d2e-6b1a-4c3e-9d7f-2a4b6c8e0f12"),
     *   @OA\Property(property="ids", type="array", maxItems=50, @OA\Items(type="integer", example=1)),
     * ),
     */
}
