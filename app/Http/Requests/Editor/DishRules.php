<?php

namespace App\Http\Requests\Editor;

use App\Enums\ProductFlag;
use App\Enums\WeightUnit;
use App\Models\DishVariant;
use Closure;
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
    use PhotoRules;

    /**
     * Rules of the dish.
     *
     * @param bool $partial whether fields may be left out (on updates)
     * @param int|null $dishId whose variants can be kept (none for a new dish)
     *
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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

            // its variants, at least one of them shown to guests
            'sizes' => [
                $presence,
                'array',
                'min:1',
                'max:10',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (is_array($value) && collect($value)->every(fn ($size) => !empty($size['is_hidden']))) {
                        $fail(DishVariant::LAST_SIZE_MESSAGE);
                    }
                },
            ],
            'sizes.*.id' => $dishId
                ? [
                    'nullable',
                    'integer',
                    'distinct',
                    // archived sizes are left as they are
                    Rule::exists('dish_variants', 'id')
                        ->where('dish_id', $dishId)
                        ->where('archived', false)
                        ->whereNull('deleted_at'),
                ]
                : ['prohibited'],
            'sizes.*.price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sizes.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'sizes.*.weight_unit' => ['nullable', 'required_with:sizes.*.weight', Rule::in(WeightUnit::getValues())],
            'sizes.*.calories' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'sizes.*.preparation_time' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'sizes.*.is_hidden' => ['sometimes', 'boolean'],

            // hidden photos count too
            'media' => ['sometimes', 'array', 'max:' . static::MAX_PHOTOS],
            ...$this->photoRules(),
        ];
    }
}
