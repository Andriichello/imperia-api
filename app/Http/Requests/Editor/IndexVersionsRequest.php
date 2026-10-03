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
