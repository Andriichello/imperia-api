<?php

namespace App\Http\Requests\Editor;

use App\Models\Dish;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class MoveDishRequest.
 *
 * Move a dish to the end of another category of the restaurant (in any of its menus).
 */
class MoveDishRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<Dish>
     */
    protected function targetClass(): string
    {
        return Dish::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $restaurantId = $this->restaurant()->id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('dish_categories', 'id')
                    ->whereNull('deleted_at')
                    // of the restaurant's menus
                    ->where(fn ($query) => $query->whereIn(
                        'menu_id',
                        fn ($menus) => $menus->select('id')
                            ->from('dish_menus')
                            ->where('restaurant_id', $restaurantId)
                            ->whereNull('deleted_at')
                    )),
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorMoveDishRequest",
     *   description="Category of the restaurant to move the dish to.",
     *   required={"category_id"},
     *   @OA\Property(property="category_id", type="integer", example=2),
     * ),
     */
}
