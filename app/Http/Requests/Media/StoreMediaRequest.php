<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Crud\StoreRequest;
use App\Models\Morphs\Media;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class StoreMediaRequest.
 */
class StoreMediaRequest extends StoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'file' => [
                    'required',
                    'file',
                    'image',
                    'max:' . config('media.max_size'),
                ],
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'title' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'description' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'disk' => [
                    'sometimes',
                    'string',
                    'in:public,google-cloud',
                ],
                'folder' => [
                    'sometimes',
                    'string',
                    'min:1',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        if (!str_starts_with($value, '/') || !str_ends_with($value, '/')) {
                            $fail("The $attribute must start and end with '/'.");
                        }

                        if (str_contains($value, '//')) {
                            $fail("The $attribute must not contain two consecutive '/'.");
                        }
                    }
                ],
                'metadata' => [
                    'nullable',
                    'object',
                ],
                'restaurant_id' => [
                    'integer',
                    'nullable',
                    'exists:restaurants,id',
                ],
            ]
        );
    }

    /**
     * Get form request fields' default values.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [
            'disk' => config('media.disk'),
            'folder' => config('media.folder'),
            'restaurant_id' => $this->user()?->restaurant_id,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="StoreMediaRequest",
     *   description="Store media request",
     *   required = {"file", "name"},
     *  @OA\MediaType(mediaType="multipart/form-data",
     *    @OA\Schema(@OA\Property(property="file", type="string", format="binary"))),
     *   @OA\Property(property="file", type="string", format="binary"),
     *   @OA\Property(property="name", type="string", example="drinks.svg"),
     *   @OA\Property(property="title", type="string", nullable="true", example="Drinks"),
     *   @OA\Property(property="description", type="string", nullable="true"),
     *   @OA\Property(property="disk", type="string", example="public",
     *      enum={"public", "google-cloud"}),
     *   @OA\Property(property="folder", type="string", example="/media/",
     *     description="Must start and end with the `/`."),
     *   @OA\Property(property="metadata", type="object", nullable="true", example="{}"),
     *  )
     */
}
