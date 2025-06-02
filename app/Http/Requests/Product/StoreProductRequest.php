<?php

namespace App\Http\Requests\Product;

use App\Enums\Hotness;
use App\Enums\WeightUnit;
use App\Http\Requests\Crud\StoreRequest;
use App\Models\Product;
use App\Models\Restaurant;
use App\Rules\SameRestaurant;
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
                    SameRestaurant::make($this->user(), Restaurant::class)
                        ->onlyAdmins(),
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
                    'max:255',
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
                'preparation_time' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'calories' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'is_vegan' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
                'is_vegetarian' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
                'has_eggs' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
                'has_nuts' => [
                    'sometimes',
                    'nullable',
                    'boolean',
                ],
                'hotness' => [
                    'sometimes',
                    'nullable',
                    'string',
                    Hotness::getValidationRule(),
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
        return SameRestaurant::make($this->user(), Restaurant::class)
            ->onlyAdmins()
            ->validate('restaurant_id', $this->get('restaurant_id'), fn() => null);
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
     *   @OA\Property(property="preparation_time", type="integer", nullable=true, example=30),
     *   @OA\Property(property="calories", type="integer", nullable=true, example=500),
     *   @OA\Property(property="is_vegan", type="boolean", nullable=true, example=true),
     *   @OA\Property(property="is_vegetarian", type="boolean", nullable=true, example=true),
     *   @OA\Property(property="has_eggs", type="boolean", nullable=true, example=false),
     *   @OA\Property(property="has_nuts", type="boolean", nullable=true, example=false),
     *   @OA\Property(property="hotness", type="string", nullable=true, example="Medium",
     *     enum={"Unknown", "Low", "Medium", "High", "Ultra"}),
     *  ),
     */
}
