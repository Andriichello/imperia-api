<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\Crud\StoreRequest;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Class StoreCommentRequest.
 */
class StoreCommentRequest extends StoreRequest
{
    public function getAllowedIncludes(): array
    {
        return array_merge(
            parent::getAllowedIncludes(),
            [
                //
            ]
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $morphs = array_keys(Relation::$morphMap);
        return array_merge(
            parent::rules(),
            [
                'text' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'commentable_id' => [
                    'required',
                    'integer',
                ],
                'commentable_type' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                    'in:' . implode(',', $morphs),
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="StoreCommentRequest",
     *   description="Store comment request",
     *   required={"text", "commentable_id", "commentable_type"},
     *   @OA\Property(property="text", type="string", example="This is a comment text..."),
     *   @OA\Property(property="commentable_id", type="integer", example=1),
     *   @OA\Property(property="commentable_type", type="string", example="customers",
     *     description="Value for it must be obrained from the target resource's type property."),
     * )
     */
}
