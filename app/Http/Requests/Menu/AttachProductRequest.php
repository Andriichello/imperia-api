<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\CrudRequest;
use App\Models\Menu;
use App\Models\Product;
use App\Rules\SameRestaurant;
use Illuminate\Validation\Rule;

/**
 * Class AttachProductRequest.
 */
class AttachProductRequest extends CrudRequest
{
    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return 'attach';
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
            'product_id' => [
                'required',
                'integer',
                Rule::exists(Product::class, 'id'),
                SameRestaurant::make($this->user(), Menu::class)
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
     *   schema="AttachProductToMenuRequest",
     *   description="Attach product to menu request.",
     *   required={"menu_id", "product_id"},
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="product_id", type="integer", example=1),
     *  ),
     */
}
