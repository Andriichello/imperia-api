<?php

namespace App\Http\Requests\Menu;

use App\Http\Requests\CrudRequest;
use App\Models\Menu;
use App\Models\Morphs\Comment;
use App\Models\Product;
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
            ],
            'product_id' => [
                'required',
                'integer',
                Rule::exists(Product::class, 'id'),
            ],
        ];
    }
}
