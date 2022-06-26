<?php

namespace App\Http\Requests\Media;

use App\Http\Requests\CrudRequest;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

/**
 * Class SetModelMediaRequest.
 */
class SetModelMediaRequest extends CrudRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $morphs = array_keys(Relation::morphMap());

        return [
            'model_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'model_type' => [
                'required',
                'string',
                'in:' . implode(',', $morphs),
            ],
            'media' => [
                'required',
                'array',
            ],
            'media.*.id' => [
                'required',
                'integer',
                'exists:media,id',
            ],
            'media.*.order' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
            ]
        ];
    }

    /**
     * Get ids of the given media.
     *
     * @return array
     */
    public function ids(): array
    {
        return Arr::pluck($this->get('media'), 'id');
    }

    /**
     * Get ability, which should be checked for the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return 'updateAttachedMedia';
    }

    /**
     * @OA\Schema(
     *   schema="AttachingMedia",
     *   description="Attaching media",
     *   required={"id"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="order", type="integer", nullable="true", example=1),
     *  ),
     *
     * @OA\Schema(
     *   schema="SetModelMediaRequest",
     *   description="Set model's media request",
     *   @OA\Property(property="model_id", type="integer", example="1"),
     *   @OA\Property(property="model_type", type="string", example="products"),
     *   @OA\Property(property="media", type="array",
     *     @OA\Items(ref ="#/components/schemas/AttachingMedia")),
     *  )
     */
}
