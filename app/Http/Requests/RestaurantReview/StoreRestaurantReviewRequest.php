<?php

namespace App\Http\Requests\RestaurantReview;

use App\Helpers\ContentLocale;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class StoreRestaurantReviewRequest.
 *
 * A guest leaves a review: a rating, and a name and a text, if they like. Their device sends its
 * token, which tells their reviews apart (only its hash is kept). `website` is a field guests
 * don't see: bots fill it in.
 */
class StoreRestaurantReviewRequest extends RestaurantReviewRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'name' => ['nullable', 'string', 'max:40'],
            'text' => ['nullable', 'string', 'max:1000'],
            'locale' => ['required', Rule::in(ContentLocale::supported())],
            'client_token' => ['required', 'string', 'min:16', 'max:100'],
            'website' => ['nullable'],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="StoreRestaurantReviewRequest",
     *   description="A guest's review: it waits for the restaurant to approve it.",
     *   required={"rating", "locale", "client_token"},
     *   @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *   @OA\Property(property="name", type="string", nullable=true, maxLength=40, example="Olena",
     *     description="None posts it as a guest."),
     *   @OA\Property(property="text", type="string", nullable=true, maxLength=1000,
     *     example="Best borscht I've had in Kyiv."),
     *   @OA\Property(property="locale", type="string", example="en", description="The language it's written in."),
     *   @OA\Property(property="client_token", type="string", minLength=16, maxLength=100,
     *     example="0f8c5d2e-6b1a-4c3e-9d7f-2a4b6c8e0f12",
     *     description="A random token of the guest's device: it asks for the status of its reviews with it."),
     *   @OA\Property(property="website", type="string", nullable=true,
     *     description="A field guests don't see: when it's filled in, the review isn't kept."),
     * ),
     */
}
