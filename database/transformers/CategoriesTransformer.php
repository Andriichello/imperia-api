<?php

namespace Database\Transformers;

use App\Imports\AbstractTransformer;

/**
 * Class CategoriesTransformer.
 */
class CategoriesTransformer extends AbstractTransformer
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

        return [
            'slug' => $record['Унікальний ідентифікатор категорії'],
            'target' => $record['Цільовий тип'],
            'popularity' => $record['Популярність'],
            'title' => $record['Назва'],
            'description' => $record['Опис'],
        ];
    }
}
