<?php

namespace App\Http\Requests\RestaurantReview;

use App\Helpers\RestaurantHelper;
use App\Http\Requests\BaseRequest;
use App\Models\Restaurant;

/**
 * Class RestaurantReviewRequest.
 *
 * Request of the public site about reviews of the restaurant of the route's `{id}` (its id or
 * slug). Anyone can make it, guests too.
 */
abstract class RestaurantReviewRequest extends BaseRequest
{
    /**
     * The restaurant the request is about.
     *
     * @var Restaurant|null
     */
    protected ?Restaurant $restaurant = null;

    /**
     * The restaurant the request is about (a restaurant, which doesn't exist, is not found).
     *
     * @return Restaurant
     */
    public function restaurant(): Restaurant
    {
        return $this->restaurant ??= RestaurantHelper::find((string) $this->route('id')) ?? abort(404);
    }

    /**
     * Anyone can make the request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The restaurant is found first: an unknown one isn't a validation error.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->restaurant();
    }
}
