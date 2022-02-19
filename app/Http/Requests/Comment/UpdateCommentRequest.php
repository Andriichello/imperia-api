<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\Crud\UpdateRequest;

/**
 * Class UpdateCommentRequest.
 */
class UpdateCommentRequest extends UpdateRequest
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
        return array_merge(
            parent::rules(),
            [
                'text' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="UpdateCommentRequest",
     *   description="Update comment request",
     *   required={"text"},
     *   @OA\Property(property="text", type="string", example="This is a comment text..."),
     * )
     */
}
