<?php

namespace App\Http\Requests\Waiter;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowWaiterRequest.
 */
class ShowWaiterRequest extends ShowRequest
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
}
