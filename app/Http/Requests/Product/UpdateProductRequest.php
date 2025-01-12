<?php

namespace App\Http\Requests\Product;

use App\Enums\WeightUnit;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Product;
use App\Models\Restaurant;
use App\Rules\SameRestaurant;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductRequest.
 */
class UpdateProductRequest extends UpdateRequest
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
                    'sometimes',
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
                        ->where('restaurant_id', data_get($this->all(), 'restaurant_id'))
                        ->whereNot('id', $this->id()),
                ],
                'badge' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'min:25',
                ],
                'title' => [
                    'sometimes',
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
                    'sometimes',
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
                    'sometimes',
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
        return SameRestaurant::make($this->user(), Restaurant::class)
            ->onlyAdmins()
            ->validate('id', $this->id(), fn() => null);
    }

    /**
     * @OA\Schema(
     *   schema="UpdateProductRequest",
     *   description="Update product request.",
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
