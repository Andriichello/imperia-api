<?php

namespace App\Http\Requests\Editor;

use App\Enums\ProductFlag;
use App\Enums\WeightUnit;
use Illuminate\Validation\Rule;

/**
 * Trait DishRules.
 *
 * Rules of a dish: its texts, visibility, flags (diet tags and allergens), sizes and photos.
 *
 * @mixin EditorRequest
 */
trait DishRules
{
    /**
     * Rules of the dish.
     *
     * @param bool $partial whether fields may be left out (on updates)
     * @param int|null $dishId whose variants can be kept (none for a new dish)
     *
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function dishRules(bool $partial, ?int $dishId): array
    {
        $presence = $partial ? 'sometimes' : 'required';

        return [
            ...$this->translationRules('title', true, 255, $partial),
            ...$this->translationRules('description', false, 300, $partial),
            ...$this->translationRules('badge', false, 25, $partial),
            'is_hidden' => ['sometimes', 'boolean'],

            'flags' => ['sometimes', 'array'],
            'flags.*' => ['string', 'distinct', Rule::in(ProductFlag::getValues())],

            // the first one is the dish itself, the others are its variants
            'sizes' => [$presence, 'array', 'min:1', 'max:10'],
            'sizes.*.id' => $dishId
                ? [
                    'nullable',
                    'integer',
                    'distinct',
                    Rule::exists('dish_variants', 'id')
                        ->where('dish_id', $dishId)
                        ->whereNull('deleted_at'),
                ]
                : ['prohibited'],
            'sizes.*.price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sizes.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'sizes.*.weight_unit' => ['nullable', 'required_with:sizes.*.weight', Rule::in(WeightUnit::getValues())],
            'sizes.*.calories' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'sizes.*.preparation_time' => ['nullable', 'integer', 'min:0', 'max:1440'],

            'media' => ['sometimes', 'array', 'max:5'],
            'media.*' => [
                'integer',
                'distinct',
                Rule::exists('media', 'id')
                    ->where('restaurant_id', $this->restaurant()->id)
                    ->whereNull('original_id'),
            ],
        ];
    }
}
