<?php

namespace Database\Importers;

use App\Imports\AbstractImporter;
use App\Imports\Sources\CsvSource;
use App\Imports\Targets\DbTarget;
use Database\Transformers\CategoriesTransformer;
use Database\Transformers\MenusTransformer;

/**
 * Class CategoriesImporter.
 */
class CategoriesImporter extends AbstractImporter
{
    /**
     * CategoriesImporter constructor.
     */
    public function __construct()
    {
        $source = new CsvSource('storage/csvs/categories.csv');
        $target = new DbTarget('categories', 'mysql');
        $transformer = new CategoriesTransformer();

        parent::__construct($source, $target, $transformer);
    }
}
