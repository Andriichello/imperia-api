<?php

namespace Database\Importers;

use App\Imports\AbstractImporter;
use App\Imports\Sources\CsvSource;
use App\Imports\Targets\DbTarget;
use Database\Transformers\MenusTransformer;

/**
 * Class MenusImporter.
 */
class MenusImporter extends AbstractImporter
{
    /**
     * MenusImporter constructor.
     */
    public function __construct()
    {
        $source = new CsvSource('storage/csvs/menus.csv');
        $target = new DbTarget('menus', 'mysql');
        $transformer = new MenusTransformer();

        parent::__construct($source, $target, $transformer);
    }
}
