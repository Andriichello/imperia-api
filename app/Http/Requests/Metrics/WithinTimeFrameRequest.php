<?php

namespace App\Http\Requests\Metrics;

use App\Http\Requests\BaseRequest;
use App\Models\Menu;
use Carbon\Carbon;

/**
 * Class WithinTimeFrameRequest.
 */
class WithinTimeFrameRequest extends BaseRequest
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
                'beg' => [
                    'sometimes',
                    'nullable',
                    'date',
                ],
                'end' => [
                    'sometimes',
                    'nullable',
                    'date',
                    'after:beg'
                ],
            ]
        );
    }

    /**
     * Get date of the time frame beginning.
     *
     * @return Carbon|null
     */
    public function beg(): ?Carbon
    {
        return $this->date('beg');
    }

    /**
     * Get date of the time frame ending.
     *
     * @return Carbon|null
     */
    public function end(): ?Carbon
    {
        return $this->date('end');
    }
}
