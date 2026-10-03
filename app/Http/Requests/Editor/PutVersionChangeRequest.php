<?php

namespace App\Http\Requests\Editor;

use App\Models\MenuVersion;
use Illuminate\Validation\Validator;
use OpenApi\Annotations as OA;

/**
 * Class PutVersionChangeRequest.
 *
 * A change of an item in a scheduled version: its fields are merged into the item's change.
 */
class PutVersionChangeRequest extends EditorRequest
{
    use VersionChangeRules;

    /**
     * Class of the model the request is about.
     *
     * @return class-string<MenuVersion>
     */
    protected function targetClass(): string
    {
        return MenuVersion::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->changeRules();
    }

    /**
     * Check the brand colors.
     *
     * @return array
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->checkBrandContrast($validator, 'fields'),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorPutVersionChangeRequest",
     *   description="A change of an item (or a new one). Its fields are merged into the item's change in the
     *     version; a field back at its live value isn't a change anymore. Fields and their values are those of
     *     the editor's requests: texts in every language, `media` as photos, `archived` to archive or restore.",
     *   required={"target_type"},
     *   @OA\Property(property="id", type="integer", nullable=true, example=null,
     *     description="Change of a new item, which is being edited."),
     *   @OA\Property(property="target_type", type="string", example="dish-variants",
     *     enum={"restaurants", "restaurant-notes", "dish-menus", "dish-categories", "dishes", "dish-variants"}),
     *   @OA\Property(property="target_id", type="integer", nullable=true, example=12,
     *     description="The changed item, none for a new one."),
     *   @OA\Property(property="parent_id", type="integer", nullable=true, example=null,
     *     description="Where a new item goes: the category of a dish, the dish of a size, the restaurant of a note."),
     *   @OA\Property(property="fields", type="object", example={"price": 195},
     *     description="New values of the fields. A new dish has `sizes` too."),
     *   @OA\Property(property="revert", type="array", @OA\Items(type="string"), example={"calories"},
     *     description="Fields, which aren't changed anymore."),
     * ),
     */
}
