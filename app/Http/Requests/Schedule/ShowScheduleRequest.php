<?php

namespace App\Http\Requests\Schedule;

use App\Http\Requests\Crud\ShowRequest;

/**
 * Class ShowScheduleRequest.
 */
class ShowScheduleRequest extends ShowRequest
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
