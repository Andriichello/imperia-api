<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;
use OpenApi\Annotations as OA;

/**
 * Class UploadRestaurantPhotoRequest.
 *
 * A photo of the restaurant (of itself, or of a dish), uploaded before it's saved
 * where it's shown. Ones, which aren't, are deleted later (`media:prune-unattached`).
 */
class UploadRestaurantPhotoRequest extends EditorRequest
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
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:' . config('media.max_size')],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="EditorUploadPhotoRequest",
     *   description="A photo: JPG, PNG or WebP, up to 10 MB.",
     *   required={"file"},
     *   @OA\Property(property="file", type="string", format="binary"),
     * )
     */
}
