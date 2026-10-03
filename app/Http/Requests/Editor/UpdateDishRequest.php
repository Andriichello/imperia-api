<?php

namespace App\Http\Requests\Editor;

use App\Models\Dish;
use OpenApi\Annotations as OA;

/**
 * Class UpdateDishRequest.
 *
 * Everything of a dish. Fields, which are left out, stay as they are. Sizes are replaced:
 * sizes, which are left out, are deleted (archived ones stay).
 */
class UpdateDishRequest extends EditorRequest
{
    use DishRules;

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
        return $this->dishRules(true, $this->target()->getKey());
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateDishRequest",
     *   description="Fields, which are left out, stay as they are. Sizes are replaced.",
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations",
     *     description="Required in the restaurant's default language."),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations",
     *     description="300 characters at most."),
     *   @OA\Property(property="badge", ref="#/components/schemas/EditorTranslations",
     *     description="25 characters at most."),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     *   @OA\Property(property="flags", type="array", @OA\Items(type="string"), example={"vegetarian", "alg-milk"}),
     *   @OA\Property(property="sizes", type="array",
     *     description="Its variants, 1 to 10 of them, at least one shown to guests. Archived ones stay as they are.",
     *     @OA\Items(
     *       required={"price"},
     *       @OA\Property(property="id", type="integer", nullable=true,
     *         description="Variant to keep (only when updating)."),
     *       @OA\Property(property="is_hidden", type="boolean", example=false),
     *       @OA\Property(property="price", type="number", example=185),
     *       @OA\Property(property="weight", type="number", nullable=true, example=300),
     *       @OA\Property(property="weight_unit", type="string", nullable=true, example="g",
     *         enum={"g", "kg", "ml", "l", "cm", "pc"}),
     *       @OA\Property(property="calories", type="integer", nullable=true, example=380),
     *       @OA\Property(property="preparation_time", type="integer", nullable=true, example=15),
     *     )),
     *   @OA\Property(property="media", type="array", @OA\Items(ref="#/components/schemas/EditorPhoto"),
     *     description="The dish's photos (the restaurant's ones) in their order, 3 at most, hidden ones included."),
     * ),
     */
}
