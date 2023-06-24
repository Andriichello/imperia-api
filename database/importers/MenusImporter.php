<?php

namespace Database\Importers;

use App\Imports\AbstractImporter;
use App\Imports\Sources\FolderOfCsvs;
use App\Imports\Targets\DbTarget;
use App\Models\Restaurant;
use Database\Transformers\MenusTransformer;

/**
 * Class MenusImporter.
 */
class MenusImporter extends AbstractImporter
{
    /**
     * Path to the folder with csvs of categories.
     *
     * @var string
     */
    protected string $path;

    /**
     * Restaurant to use when storing products.
     *
     * @var Restaurant|null
     */
    protected ?Restaurant $restaurant;

    /**
     * MenusImporter constructor.
     *
     * @param Restaurant|null $restaurant
     * @param string $path
     */
    public function __construct(
        ?Restaurant $restaurant = null,
        string $path = 'storage/csvs/menus'
    ) {
        $this->path = $path;
        $this->restaurant = $restaurant;

        $source = new FolderOfCsvs($path);
        $target = new DbTarget('menus', 'mysql');
        $transformer = new MenusTransformer();

        parent::__construct($source, $target, $transformer);
    }

    /**
     * Perform any additional actions before importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function before(array &$record): void
    {
        if (!isset($record['restaurant_id'])) {
            $record['restaurant_id'] = $this->restaurant->id;
        }
    }
}
