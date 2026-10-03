<?php

namespace App\Http\Requests\Editor;

use Illuminate\Validation\Rule;

/**
 * Trait PhotoRules.
 *
 * Rules of photos of the restaurant or a dish, in their order, each one shown to guests or
 * hidden: `[{"id": 4, "is_hidden": false}]`. They're uploaded beforehand
 * (`POST /api/editor/restaurants/{id}/media`), so they have to be the restaurant's.
 *
 * @mixin EditorRequest
 */
trait PhotoRules
{
    /**
     * Photos a dish can have, hidden ones included.
     *
     * @var int
     */
    public const MAX_PHOTOS = 3;

    /**
     * Rules of the photos' items (the list itself has its own limits).
     *
     * @param string $field
     *
     * @return array
     */
    protected function photoRules(string $field = 'media'): array
    {
        return [
            "$field.*" => ['array:id,is_hidden'],
            "$field.*.id" => [
                'required',
                'integer',
                'distinct',
                Rule::exists('media', 'id')
                    ->where('restaurant_id', $this->restaurant()->id)
                    ->whereNull('original_id'),
            ],
            "$field.*.is_hidden" => ['sometimes', 'boolean'],
        ];
    }
}
