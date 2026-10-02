<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class OrderMenusRequest.
 *
 * Order of the restaurant's menus, and of the categories in each of them. A category listed
 * under another menu is moved there (with its dishes). Menus and categories, which are left
 * out (e.g. archived ones), keep their places.
 */
class OrderMenusRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<Restaurant>
     */
    protected function targetClass(): string
    {
        return Restaurant::class;
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
            'menus' => ['present', 'array'],
            'menus.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('dish_menus', 'id')
                    ->where('restaurant_id', $restaurantId)
                    ->whereNull('deleted_at'),
            ],
            // hidden from guests, or shown again (left out: as it is)
            'menus.*.is_hidden' => ['sometimes', 'boolean'],
            'menus.*.categories' => ['present', 'array'],
            'menus.*.categories.*' => [
                'integer',
                'distinct',
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
     *   schema="EditorOrderMenusRequest",
     *   description="Menus and their categories in their new order. Categories listed under another menu move there.
     *     Menus can be hidden or shown at the same time.",
     *   required={"menus"},
     *   @OA\Property(property="menus", type="array", @OA\Items(
     *     required={"id", "categories"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="is_hidden", type="boolean", example=false),
     *     @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={3, 1, 2}),
     *   )),
     * ),
     */
}
