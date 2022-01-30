<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;
use App\Http\Requests\Traits\WithTarget;

/**
 * Class DestroyRequest.
 */
class DestroyRequest extends CrudRequest
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
                'force' => [
                    'boolean',
                ],
            ]
        );
    }

    /**
     * Get force parameter.
     *
     * @return bool
     */
    public function force(): bool
    {
        return (bool) $this->get('force', false);
    }
}
