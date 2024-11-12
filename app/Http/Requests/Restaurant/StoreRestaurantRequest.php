<?php

namespace App\Http\Requests\Restaurant;

use App\Http\Requests\Crud\StoreRequest;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Class StoreRestaurantRequest.
 */
class StoreRestaurantRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'min:2',
                Rule::unique(Restaurant::class, 'slug'),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'country' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'city' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'place' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'timezone' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/',
            ],
            'location' => [
                'sometimes',
                'nullable',
                'url',
            ],
            'website' => [
                'sometimes',
                'nullable',
                'url',
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        if (!$user || !$user->isAdmin()) {
            return false;
        }

        return $user->restaurant_id === null;
    }

    /**
     * @OA\Schema(
     *   schema="StoreRestaurantRequest",
     *   description="Store restaurant request",
     *   required={"slug", "name", "country", "city", "place", "timezone"},
     *   @OA\Property(property="slug", type="string", example="imperia",
     *     description="Must be unique within the database."),
     *   @OA\Property(property="name", type="string", example="Imperia"),
     *   @OA\Property(property="country", type="string", example="Ukraine"),
     *   @OA\Property(property="city", type="string", example="Uzhhorod"),
     *   @OA\Property(property="place", type="string", example="Dovzhenka St, 12"),
     *   @OA\Property(property="timezone", type="string", example="Europe/Kyiv"),
     *   @OA\Property(property="email", type="string", nullable=true, example="imperia@email.com"),
     *   @OA\Property(property="phone", type="string", nullable=true, example="+380507777777",
     *     description="Phone number may start with a plus and must contain only digits 0-9."),
     *   @OA\Property(property="location", type="string", nullable=true,
     *     example="https://maps.app.goo.gl/uPWnGp3eTw2LGctEA",
     *     description="Link to restaurant's location on Google Maps."),
     *   @OA\Property(property="website", type="string", nullable=true,
     *     description="Link to restaurant's website."),
     * )
     */
}
