<?php

namespace App\Http\Requests\Editor;

use App\Models\DishCategory;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class UpdateCategoryRequest.
 *
 * Texts and visibility of a category, and the order of its dishes (those left out, e.g.
 * archived ones, keep their places). Fields, which are left out, stay as they are.
 */
class UpdateCategoryRequest extends EditorRequest
{
    use MenuRules;

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
            ...$this->textRules(true),
            'dishes' => ['sometimes', 'array'],
            'dishes.*' => [
                'integer',
                'distinct',
                Rule::exists('dishes', 'id')
                    ->where('category_id', $this->target()->getKey())
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateCategoryRequest",
     *   description="Fields, which are left out, stay as they are.",
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations",
     *     description="Required in the restaurant's default language."),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     *   @OA\Property(property="dishes", type="array", @OA\Items(type="integer"), example={3, 1, 2},
     *     description="Ids of the category's dishes in their new order."),
     * ),
     */
}
