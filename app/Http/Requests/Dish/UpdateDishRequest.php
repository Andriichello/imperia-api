<?php

namespace App\Http\Requests\Dish;

use App\Enums\WeightUnit;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Rules\SameRestaurant;
use Illuminate\Validation\Rule;

/**
 * Class UpdateDishRequest.
 */
class UpdateDishRequest extends UpdateRequest
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
                'menu_id' => [
                    'sometimes',
                    'integer',
                    Rule::exists(DishMenu::class, 'id'),
                    SameRestaurant::make($this->user(), DishMenu::class)
                        ->onlyAdmins(),
                ],
                'category_id' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    Rule::exists(DishCategory::class, 'id'),
                ],
                'slug' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                    Rule::unique(Dish::class, 'slug')
                        ->where('menu_id', data_get($this->all(), 'menu_id'))
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
                    'max:255',
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
                'calories' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
                ],
                'preparation_time' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0',
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
        return SameRestaurant::make($this->user(), Dish::class)
            ->onlyAdmins()
            ->validate('id', $this->id(), fn() => null);
    }

    /**
     * @OA\Schema(
     *   schema="UpdateDishRequest",
     *   description="Update dish request.",
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="category_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="slug", type="string", nullable=true,
     *     description="Must be unique within the menu."),
     *   @OA\Property(property="badge", type="string", nullable=true),
     *   @OA\Property(property="title", type="string", example="Tomato Soup"),
     *   @OA\Property(property="description", type="string", nullable=true),
     *   @OA\Property(property="price", type="number", example=125),
     *   @OA\Property(property="weight", type="number", example=200),
     *   @OA\Property(property="weight_unit", type="string", example="g",
     *     enum={"g", "kg", "ml", "l", "cm", "pc"}),
     *   @OA\Property(property="calories", type="integer", nullable=true, example=500),
     *   @OA\Property(property="preparation_time", type="integer", nullable=true, example=30),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", example=1),
     *   @OA\Property(property="metadata", type="object", nullable=true),
     *  ),
     */
}
