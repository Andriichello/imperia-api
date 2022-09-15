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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->mergeIfMissing([
            'from' => now()->setTime(0, 0),
        ]);
    }
}
