<?php

namespace App\Http\Requests\Editor;

use App\Enums\Establishment;
use App\Enums\ProductFlag;
use App\Enums\WeightUnit;
use App\Helpers\ColorHelper;
use App\Models\DishVariant;
use App\Models\MenuVersionChange;
use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Trait VersionChangeRules.
 *
 * Rules of a change in a scheduled version: the item (its type and id, or the parent of a new
 * one) and its fields' new values, which are checked like the editor checks them when saving.
 *
 * @mixin EditorRequest
 */
trait VersionChangeRules
{
    use PhotoRules;

    /**
     * Rules of a change at the path (e.g. `changes.0`, or none for the request itself).
     *
     * @param string|null $path
     *
     * @return array
     */
    protected function changeRules(?string $path = null): array
    {
        $prefix = $path === null ? '' : "$path.";
        $type = $this->input("{$prefix}target_type");
        $kinds = MenuVersionChange::fieldsOf((string) $type);
        // a change given by its id is a new item's one
        $isNew = !$this->input("{$prefix}target_id");

        // sizes are given only for a new dish, a new item isn't archived
        $fields = array_keys(array_filter(
            $kinds,
            fn (string $kind) => match ($kind) {
                MenuVersionChange::KIND_SIZES => $isNew,
                MenuVersionChange::KIND_ARCHIVED => !$isNew,
                default => true,
            }
        ));

        return [
            "{$prefix}id" => ['nullable', 'integer'],
            "{$prefix}target_type" => ['required', 'string', Rule::in(array_keys(MenuVersionChange::TARGETS))],
            "{$prefix}target_id" => ['nullable', 'integer'],
            "{$prefix}parent_id" => ['nullable', 'integer'],
            "{$prefix}fields" => ['sometimes', 'array:' . implode(',', $fields)],
            "{$prefix}revert" => ['sometimes', 'array'],
            "{$prefix}revert.*" => ['string', Rule::in(array_keys($kinds))],
            ...$this->fieldRules("{$prefix}fields", (string) $type),
        ];
    }

    /**
     * Rules of the fields of an item type, which are checked like the editor's own ones.
     *
     * @param string $path
     * @param string $type
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function fieldRules(string $path, string $type): array
    {
        $hex = 'regex:/^#[0-9a-fA-F]{6}$/';
        $boolean = ['sometimes', 'boolean'];

        return match ($type) {
            'restaurants' => [
                ...$this->translationRules("$path.name", true, 255, true),
                ...$this->translationRules("$path.address", false, 255, true),
                "$path.establishment" => ['sometimes', 'string', Rule::in(Establishment::getValues())],
                "$path.phone" => ['sometimes', 'nullable', 'string', 'max:30'],
                "$path.brand_primary" => ['sometimes', 'string', $hex, "required_with:$path.brand_primary_content"],
                "$path.brand_primary_content" => ['sometimes', 'string', $hex, "required_with:$path.brand_primary"],
                "$path.media" => ['sometimes', 'array', 'max:20'],
                ...$this->photoRules("$path.media"),
            ],
            'restaurant-notes' => [
                ...$this->translationRules("$path.text", true, 120, true),
                "$path.is_hidden" => $boolean,
            ],
            'dish-menus', 'dish-categories' => [
                ...$this->translationRules("$path.title", true, 255, true),
                ...$this->translationRules("$path.description", false, 1000, true),
                "$path.is_hidden" => $boolean,
                "$path.archived" => $boolean,
            ],
            'dishes' => [
                ...$this->translationRules("$path.title", true, 255, true),
                ...$this->translationRules("$path.description", false, 300, true),
                ...$this->translationRules("$path.badge", false, 25, true),
                "$path.is_hidden" => $boolean,
                "$path.archived" => $boolean,
                "$path.flags" => ['sometimes', 'array'],
                "$path.flags.*" => ['string', 'distinct', Rule::in(ProductFlag::getValues())],
                "$path.media" => ['sometimes', 'array', 'max:' . static::MAX_PHOTOS],
                ...$this->photoRules("$path.media"),
                "$path.sizes" => ['sometimes', 'array', 'min:1', 'max:10', $this->someSizeIsShown()],
                ...$this->sizeRules("$path.sizes.*"),
            ],
            'dish-variants' => [
                ...$this->sizeRules($path, true),
                "$path.archived" => $boolean,
            ],
            default => [],
        };
    }

    /**
     * Rules of a size's values.
     *
     * @param string $path
     * @param bool $partial whether values may be left out
     *
     * @return array
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function sizeRules(string $path, bool $partial = false): array
    {
        return [
            "$path.price" => [$partial ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:999999.99'],
            "$path.weight" => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100000'],
            "$path.weight_unit" => ['sometimes', 'nullable', Rule::in(WeightUnit::getValues())],
            "$path.calories" => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100000'],
            "$path.preparation_time" => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1440'],
            "$path.is_hidden" => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Rule, which keeps a size of a new dish shown to guests.
     *
     * @return Closure
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function someSizeIsShown(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (is_array($value) && collect($value)->every(fn ($size) => !empty($size['is_hidden']))) {
                $fail(DishVariant::LAST_SIZE_MESSAGE);
            }
        };
    }

    /**
     * Check, that brand colors of the restaurant (when they're changed) are readable.
     *
     * @param Validator $validator
     * @param string $path of the fields
     *
     * @return void
     */
    protected function checkBrandContrast(Validator $validator, string $path): void
    {
        $primary = $this->input("$path.brand_primary");
        $text = $this->input("$path.brand_primary_content");

        if (!ColorHelper::isHex($primary) || !ColorHelper::isHex($text)) {
            return;
        }

        $contrast = ColorHelper::contrastOnTints($primary, $text);

        if ($contrast < ColorHelper::READABLE) {
            $validator->errors()->add("$path.brand_primary_content", sprintf(
                'Text on the tinted backgrounds has %.1f : 1 contrast, it needs at least %.1f : 1.',
                floor($contrast * 10) / 10,
                ColorHelper::READABLE
            ));
        }
    }
}
