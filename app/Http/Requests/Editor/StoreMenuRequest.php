<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use OpenApi\Annotations as OA;

/**
 * Class StoreMenuRequest.
 *
 * A new menu of the restaurant, added at the end of its menus.
 */
class StoreMenuRequest extends EditorRequest
{
    use MenuRules;

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
        return $this->textRules(false);
    }

    /**
     * @OA\Schema(
     *   schema="EditorStoreMenuRequest",
     *   description="A new one goes at the end of the list.",
     *   required={"title"},
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations",
     *     description="Required in the restaurant's default language."),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     * ),
     */
}
