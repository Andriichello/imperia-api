<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class UpdateRestaurantPhotosRequest.
 *
 * Ids of the restaurant's photos, in their order (the first one is the cover).
 * Photos are uploaded beforehand (`POST /api/media`), they have to be the restaurant's.
 */
class UpdateRestaurantPhotosRequest extends EditorRequest
{
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
            'media.*' => [
                'integer',
                'distinct',
                Rule::exists('media', 'id')
                    ->where('restaurant_id', $this->restaurant()->id)
                    ->whereNull('original_id'),
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorUpdateRestaurantPhotosRequest",
     *   description="Ids of the restaurant's photos in their order, the first one is the cover.",
     *   required={"media"},
     *   @OA\Property(property="media", type="array", @OA\Items(type="integer"), example={3, 1, 2}),
     * ),
     */
}
