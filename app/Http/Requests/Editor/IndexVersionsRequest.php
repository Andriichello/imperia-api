<?php

namespace App\Http\Requests\Editor;

use App\Models\Restaurant;

/**
 * Class IndexVersionsRequest.
 *
 * Scheduled versions of the restaurant.
 */
class IndexVersionsRequest extends EditorRequest
{
    /**
     * Class of the model the request is about.
     *
     * @return class-string<Restaurant>
     */
    protected function targetClass(): string
    {
        return Restaurant::class;
    }

    /**
     * The flags of the query as booleans (`?pending=true` too).
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('pending')) {
            $this->merge([
                'pending' => filter_var($this->input('pending'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'pending' => ['sometimes', 'boolean'],
        ];
    }
}
