<?php

namespace App\Http\Requests\Editor;

use App\Models\DishMenu;
use OpenApi\Annotations as OA;

/**
 * Class StoreCategoryRequest.
 *
 * A new category of the menu, added at the end of its categories.
 */
class StoreCategoryRequest extends EditorRequest
{
    use MenuRules;

    /**
     * Class of the model the request is about.
     *
     * @return class-string<DishMenu>
     */
    protected function targetClass(): string
    {
        return DishMenu::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->textRules(false);
    }

    /**
     * @OA\Schema(
     *   schema="EditorStoreCategoryRequest",
     *   description="A new one goes at the end of the list.",
     *   required={"title"},
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations",
     *     description="Required in the restaurant's default language."),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     * ),
     */
}
