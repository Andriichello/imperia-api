<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Crud\UpdateRequest;

/**
 * Class UpdateMediaRequest.
 */
class UpdateMediaRequest extends UpdateRequest
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
                    'sometimes',
                    'file',
                    'image',
                    'max:10240', // 10MB
                ],
                'name' => [
                    'sometimes',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'title' => [
                    'sometimes',
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

    /**
     * @OA\Schema(
     *   schema="UpdateMediaRequest",
     *   description="Update media request",
     *   @OA\Property(property="file", type="file"),
     *   @OA\Property(property="name", type="string", example="drinks.svg"),
     *   @OA\Property(property="title", type="string", nullable="true", example="Drinks"),
     *   @OA\Property(property="description", type="string", nullable="true"),
     *   @OA\Property(property="folder", type="string", example="/media/",
     *     description="Must start and end with the `/`."),
     *   @OA\Property(property="metadata", type="object", nullable="true", example="{}"),
     *  )
     */
}
