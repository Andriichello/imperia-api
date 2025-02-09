<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Morphs\Media;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

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
                    'max:' . config('media.max_size'),
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

    public function withValidator(Validator $validator)
    {
        /** @var Media $media */
        $media = $this->targetOrFail(Media::class);

        $validator->sometimes(
            'name',
            [
                Rule::unique(Media::class, 'name')
                    ->where('folder', $this->get('folder', $media->folder))
                    ->where('disk', $this->get('disk', $media->disk))
                    ->where('name', $this->get('name', $media->name))
                    ->ignore($media->id),
            ],
            function () use ($media) {
                if ($this->has('name') && $media->name !== $this->get('name')) {
                    return true;
                }

                return $this->has('folder') && $media->folder !== $this->get('folder');
            }
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
     *   @OA\Property(property="metadata", nullable="true",
     *     ref ="#/components/schemas/MediaMetadata"),
     *  )
     */
}
