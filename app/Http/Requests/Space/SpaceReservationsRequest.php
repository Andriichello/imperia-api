<?php

namespace App\Http\Requests\Space;

use App\Http\Requests\Crud\ShowRequest;
use Carbon\Carbon;

/**
 * Class SpaceReservationsRequest.
 */
class SpaceReservationsRequest extends ShowRequest
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
                'order_id' => [
                    'sometimes',
                    'integer',
                    'exists:orders,id',
                ],
                'start_at' => [
                    'required',
                    'date',
                ],
                'end_at' => [
                    'sometimes',
                    'date',
                    'after_or_equal:start_at',
                ],
            ]
        );
    }

    public function getStartAt(): Carbon
    {
        return new Carbon($this->get('start_at'));
    }

    public function getEndAt(): Carbon
    {
        return new Carbon($this->get('end_at', $this->getStartAt()));
    }
}
