<?php

namespace App\Http\Requests\Restaurant;

/**
 * Class GetHolidaysRequest.
 */
class GetHolidaysRequest extends ShowRestaurantRequest
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
                'from' => [
                    'sometimes',
                    'date',
                ]
            ]
        );
    }

    /**
     * Get form request fields' default values.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [
            'from' => now()->setTime(0, 0),
        ];
    }
}
