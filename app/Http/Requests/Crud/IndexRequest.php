<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;

/**
 * Class IndexRequest.
 */
class IndexRequest extends CrudRequest
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
                //
            ]
        );
    }

    /**
     * @OA\Schema(
     *   schema="DeletedParameter",
     *   description="Query parameter, which determines if soft-deleted records should be
    fetched from database. Available relations: `only`, `with` and `without`, which is a default one.",
     *   enum={"only", "with", "without"},
     *   type="string", example="without"
     * )
     */
}
