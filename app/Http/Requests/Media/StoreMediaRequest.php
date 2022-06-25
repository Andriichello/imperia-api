<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Crud\StoreRequest;

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
                    'max:10240', // 10MB
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
            ]
        );
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'disk' => env('FILESYSTEM_MEDIA', 'public'),
            'folder' => '/media/uploaded/',
        ]);
    }

    /**
     * @OA\Schema(
     *   schema="StoreMediaRequest",
     *   description="Store media request",
     *   required = {"file", "name", "folder"},
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
