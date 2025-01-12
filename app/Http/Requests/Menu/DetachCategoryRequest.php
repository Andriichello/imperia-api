<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\CrudRequest;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Rules\SameRestaurant;
use Illuminate\Validation\Rule;

/**
 * Class AttachProductRequest.
 */
class DetachCategoryRequest extends CrudRequest
{
    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return 'detach';
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
                Rule::exists(Menu::class, 'id'),
                SameRestaurant::make($this->user(), Menu::class)
                    ->onlyAdmins(),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists(Category::class, 'id'),
                SameRestaurant::make($this->user(), Category::class)
                    ->onlyAdmins(),
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
        return $this->isByAdmin();
    }

    /**
     * @OA\Schema(
     *   schema="DetachCategoryFromMenuRequest",
     *   description="Detach category from menu request.",
     *   required={"menu_id", "category_id"},
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="category_id", type="integer", example=1),
     *  ),
     */
}
