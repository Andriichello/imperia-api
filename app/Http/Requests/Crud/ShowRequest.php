<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;
use App\Http\Requests\Traits\WithTarget;

/**
 * Class ShowRequest.
 */
class ShowRequest extends CrudRequest
{
    use WithTarget;

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
}
