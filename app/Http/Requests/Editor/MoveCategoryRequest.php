<?php

namespace App\Http\Requests\Editor;

use App\Models\DishCategory;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class MoveCategoryRequest.
 *
 * Move a category (with its dishes) to the end of another menu of the restaurant.
 */
class MoveCategoryRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<DishCategory>
     */
    protected function targetClass(): string
    {
        return DishCategory::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'menu_id' => [
                'required',
                'integer',
                Rule::exists('dish_menus', 'id')
                    ->where('restaurant_id', $this->restaurant()->id)
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorMoveCategoryRequest",
     *   description="Menu of the restaurant to move the category (with its dishes) to.",
     *   required={"menu_id"},
     *   @OA\Property(property="menu_id", type="integer", example=2),
     * ),
     */
}
