<?php

namespace App\Http\Requests\Editor;

use App\Models\DishMenu;
use OpenApi\Annotations as OA;

/**
 * Class UpdateMenuRequest.
 *
 * Texts and visibility of a menu. Fields, which are left out, stay as they are.
 */
class UpdateMenuRequest extends EditorRequest
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
        return $this->textRules(true);
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateMenuRequest",
     *   description="Fields, which are left out, stay as they are.",
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations",
     *     description="Required in the restaurant's default language."),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     * ),
     */
}
