<?php

namespace App\Http\Requests\Product;

use App\Enums\WeightUnit;
use App\Http\Requests\Crud\StoreRequest;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Class StoreProductRequest.
 */
class StoreProductRequest extends StoreRequest
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
                'restaurant_id' => [
                    'required',
                    'integer',
                    Rule::exists(Restaurant::class, 'id'),
                ],
                'slug' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                    Rule::unique(Product::class, 'slug')
                        ->where('restaurant_id', data_get($this->all(), 'restaurant_id')),
                ],
                'badge' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'min:25',
                ],
                'title' => [
                    'required',
                    'string',
                    'min:1',
                    'min:255',
                ],
                'description' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                ],
                'price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
                'weight' => [
                    'sometimes',
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'weight_unit' => [
                    'required_with:weight',
                    'nullable',
                    'string',
                    WeightUnit::getValidationRule(),
                ],
                'archived' => [
                    'sometimes',
                    'boolean',
                ],
                'popularity' => [
                    'sometimes',
                    'integer',
                ],
                'metadata' => [
                    'sometimes',
                    'nullable',
                    'array',
                ],
            ]
        );
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

        return $user->restaurant_id === null
            || $user->restaurant_id === ((int) $this->get('restaurant_id'));
    }

    /**
      * @OA\Schema(
     *   schema="StoreProductRequest",
     *   description="Store product request.",
     *   required={"restaurant_id", "title", "price"},
     *   @OA\Property(property="restaurant_id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", nullable=true,
     *     description="Must be unique within the restaurant."),
     *   @OA\Property(property="badge", type="string", nullable=true),
     *   @OA\Property(property="title", type="string", example="Tomato Soup"),
     *   @OA\Property(property="description", type="string", nullable=true),
     *   @OA\Property(property="price", type="number", example=125),
     *   @OA\Property(property="weight", type="number", example=200),
     *   @OA\Property(property="weight_unit", type="string", example="g",
     *     enum={"g", "kg", "ml", "l", "cm", "pc"}),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", example=1),
     *   @OA\Property(property="metadata", type="object", nullable=true),
     *  ),
     */
}
