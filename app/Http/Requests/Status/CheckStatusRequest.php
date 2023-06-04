<?php

namespace App\Http\Requests\Status;

use App\Http\Requests\BaseRequest;

/**
 * Class CheckStatusRequest.
 */
class CheckStatusRequest extends BaseRequest
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
                'db' => [
                    'sometimes',
                    'bool',
                ],
            ]
        );
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->mergeIfMissing(['db' => true]);
    }
}
