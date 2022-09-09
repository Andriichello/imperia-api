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
                'days' => [
                    'sometimes',
                    'integer',
                    'min:1',
                    'max:31',
                ],
                'from' => [
                    'sometimes',
                    'date',
                ]
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
        $this->mergeIfMissing([
            'days' => 1,
            'from' => now()->setTime(0, 0),
        ]);
    }
}
