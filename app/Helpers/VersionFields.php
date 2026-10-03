<?php

namespace App\Helpers;

use App\Models\BaseModel;
use App\Models\Interfaces\MediableInterface;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\MenuVersionChange;
use App\Models\Morphs\Media;

/**
 * Class VersionFields.
 *
 * Values of the fields scheduled versions change (see `MenuVersionChange::TARGETS`): they're
 * normalized, so that planned, live and stored (JSON) values can be compared as they are.
 */
class VersionFields
{
    /**
     * Normalize a value of the field kind.
     *
     * @param string $kind
     * @param mixed $value
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function normalize(string $kind, mixed $value): mixed
    {
        return match ($kind) {
            MenuVersionChange::KIND_TEXT => static::texts($value),
            MenuVersionChange::KIND_PRICE => is_numeric($value) ? round((float) $value, 2) : null,
            MenuVersionChange::KIND_WEIGHT => is_numeric($value) ? (string) ($value + 0) : null,
            MenuVersionChange::KIND_NUMBER => is_numeric($value) ? (int) $value : null,
            MenuVersionChange::KIND_HIDDEN, MenuVersionChange::KIND_ARCHIVED => (bool) $value,
            MenuVersionChange::KIND_FLAGS => static::flags($value),
            MenuVersionChange::KIND_MEDIA => static::media($value),
            MenuVersionChange::KIND_SIZES => static::sizes($value),
            default => static::value($value),
        };
    }

    /**
     * Whether two values of the field kind are the same.
     *
     * @param string $kind
     * @param mixed $first
     * @param mixed $second
     *
     * @return bool
     */
    public static function same(string $kind, mixed $first, mixed $second): bool
    {
        return static::normalize($kind, $first) === static::normalize($kind, $second);
    }

    /**
     * The field's live value of the item.
     *
     * @param BaseModel $target
     * @param string $field
     * @param string $kind
     *
     * @return mixed
     */
    public static function live(BaseModel $target, string $field, string $kind): mixed
    {
        $value = match ($kind) {
            MenuVersionChange::KIND_TEXT => $target instanceof TranslatableInterface
                ? $target->getTranslations($field)
                : null,
            MenuVersionChange::KIND_MEDIA => $target instanceof MediableInterface
                ? static::photos($target)
                : [],
            default => $target->getAttribute($field),
        };

        return static::normalize($kind, $value);
    }

    /**
     * Photos of the model in their order, each shown or hidden.
     *
     * @param MediableInterface $model
     *
     * @return array<int, array{id: int, is_hidden: bool}>
     */
    protected static function photos(MediableInterface $model): array
    {
        $photos = [];

        /** @var Media $media */
        foreach ($model->allMedia()->get() as $media) {
            $photos[] = ['id' => $media->id, 'is_hidden' => (bool) data_get($media, 'pivot.is_hidden')];
        }

        return $photos;
    }

    /**
     * Texts in every content language, empty ones are null.
     *
     * @param mixed $value
     *
     * @return array<string, string|null>
     */
    public static function texts(mixed $value): array
    {
        $texts = [];

        foreach (ContentLocale::supported() as $locale) {
            $text = is_array($value) ? ($value[$locale] ?? null) : null;
            $text = is_scalar($text) ? trim((string) $text) : '';

            $texts[$locale] = $text === '' ? null : $text;
        }

        return $texts;
    }

    /**
     * A plain value: a trimmed string, empty ones are null.
     *
     * @param mixed $value
     *
     * @return string|null
     */
    public static function value(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value === '' ? null : $value;
    }

    /**
     * Flags without repetitions, in a stable order.
     *
     * @param mixed $value
     *
     * @return string[]
     */
    public static function flags(mixed $value): array
    {
        $flags = array_values(array_unique(array_map('strval', is_array($value) ? $value : [])));
        sort($flags);

        return $flags;
    }

    /**
     * Photos in their order, each shown or hidden.
     *
     * @param mixed $value
     *
     * @return array<int, array{id: int, is_hidden: bool}>
     */
    public static function media(mixed $value): array
    {
        return array_values(array_map(fn ($item) => [
            'id' => (int) (is_array($item) ? ($item['id'] ?? 0) : $item),
            'is_hidden' => is_array($item) && !empty($item['is_hidden']),
        ], is_array($value) ? $value : []));
    }

    /**
     * Sizes of a new dish, each with the values of a size.
     *
     * @param mixed $value
     *
     * @return array<int, array>
     */
    public static function sizes(mixed $value): array
    {
        $kinds = MenuVersionChange::fieldsOf('dish-variants');

        return array_values(array_map(function ($size) use ($kinds) {
            $values = [];

            foreach (['price', 'weight', 'weight_unit', 'calories', 'preparation_time', 'is_hidden'] as $field) {
                $values[$field] = static::normalize($kinds[$field], is_array($size) ? ($size[$field] ?? null) : null);
            }

            if ($values['weight'] === null) {
                $values['weight_unit'] = null;
            }

            return $values;
        }, is_array($value) ? $value : []));
    }
}
