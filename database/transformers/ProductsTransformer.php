<?php

namespace Database\Transformers;

use App\Enums\WeightUnit;
use App\Imports\AbstractTransformer;

/**
 * Class CategoriesTransformer.
 */
class ProductsTransformer extends AbstractTransformer
{
    /**
     * Transform record to be imported.
     *
     * @param array $record
     *
     * @return array
     */
    public function transform(array $record): array
    {
        $record = parent::transform($record);

        $weight = $record['Вага'];
        $weightUnit = null;

        if (is_string($weight)) {
            [$weight, $weightUnit] = $this->parseWeight($weight);
        }

        return [
            'slug' => $record['Унікальний ідентифікатор страви'],
            'popularity' => $record['Популярність'],
            'title' => $record['Назва'],
            'description' => $record['Опис'],
            'price' => $record['Ціна'],
            'weight' => $weight,
            'weight_unit' => $weightUnit,
            'metadata' => json_encode([
                'menu_slug' => $record['Унікальний ідентифікатор меню'],
                'category_slug' => $record['Унікальний ідентифікатор категорії'],
            ]),
        ];
    }

    /**
     * Get weight and weight_unit from string value.
     *
     * @param string|null $str
     *
     * @return array
     */
    public function parseWeight(?string $str): array
    {
        $matches = [];
        preg_match('/(?<val>[+-]?([0-9]*[.])?[0-9]+)\s*(?<unit>\S*)?/', $str, $matches);

        $val = isset($matches['val']) && is_numeric($matches['val'])
            ? (float) $matches['val'] : null;

        $unit = $matches['unit'] ?? null;

        if ($unit) {
            $unit = strtolower($unit);

            foreach (WeightUnit::getValues() as $const) {
                $units = [
                    $const,
                    trans('enum.weight_unit.' . $const, [], 'uk'),
                ];

                if (in_array($unit, $units)) {
                    $weightUnit = $const;
                    break;
                }
            }
        }

        return [$val, $weightUnit ?? null];
    }
}
