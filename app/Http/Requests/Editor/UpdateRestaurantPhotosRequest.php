<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use OpenApi\Annotations as OA;

/**
 * Class UpdateRestaurantPhotosRequest.
 *
 * The restaurant's photos, in their order (the first one shown is the cover), each one shown or hidden.
 * Photos are uploaded beforehand (`POST /api/editor/restaurants/{id}/media`), they have to be the restaurant's.
 */
class UpdateRestaurantPhotosRequest extends EditorRequest
{
    use PhotoRules;

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
        return [
            'media' => ['present', 'array', 'max:20'],
            ...$this->photoRules(),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorPhoto",
     *   description="A photo in a gallery: hidden ones are kept, but guests don't see them.",
     *   required={"id"},
     *   @OA\Property(property="id", type="integer", example=3),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     * ),
     * @OA\Schema(
     *   schema="EditorUpdateRestaurantPhotosRequest",
     *   description="The restaurant's photos in their order, the first one shown is the cover.",
     *   required={"media"},
     *   @OA\Property(property="media", type="array", @OA\Items(ref="#/components/schemas/EditorPhoto"),
     *     description="20 at most."),
     * ),
     */
}
