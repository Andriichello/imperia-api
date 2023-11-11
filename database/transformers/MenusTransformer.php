<?php

namespace Database\Transformers;

use App\Imports\AbstractTransformer;

/**
 * Class MenusTransformer.
 */
class MenusTransformer extends AbstractTransformer
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
            'slug' => $record['Унікальний ідентифікатор меню'],
            'popularity' => $record['Популярність'],
            'title' => $record['Назва'],
            'description' => $record['Опис'],
        ];
    }
}
