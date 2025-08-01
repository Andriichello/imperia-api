<?php

namespace Database\Importers;

use App\Imports\AbstractImporter;
use App\Imports\Exceptions\SkipRecord;
use App\Imports\Sources\FolderOfCsvs;
use App\Imports\Targets\DbTarget;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\Restaurant;
use Database\Transformers\ProductsTransformer;
use Exception;

/**
 * Class ProductsImporter.
 */
class ProductsImporter extends AbstractImporter
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
     * Products constructor.
     *
     * @param Restaurant|null $restaurant
     * @param string $path
     */
    public function __construct(
        ?Restaurant $restaurant = null,
        string $path = 'storage/csvs/products'
    ) {
        $this->path = $path;
        $this->restaurant = $restaurant;

        $source = new FolderOfCsvs($path);
        $target = new DbTarget('products', 'mysql');
        $transformer = new ProductsTransformer();

        parent::__construct($source, $target, $transformer);
    }

    /**
     * Perform any additional actions before importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     * @throws SkipRecord
     * @throws Exception
     * @SuppressWarnings(PHPMD)
     */
    public function before(array &$record): void
    {
        if (!isset($record['restaurant_id'])) {
            $record['restaurant_id'] = $this->restaurant?->id;
        }

        // this is not a new product, but a different variant of the previous one
        if ($this->lastRecord && $this->lastRecord['slug'] === $record['slug']) {
            if (!isset($record['weight']) || !isset($record['weight_unit'])) {
                throw new Exception(
                    'Product variant must have weight and weight unit specified.'
                    . ' Failed for record: ' . json_encode($record)
                );
            }

            $variant = [
                'price' => $record['price'],
                'weight' => $record['weight'],
                'weight_unit' => $record['weight_unit'],
            ];

            /** @var Product|null $product */
            $product = Product::query()
                ->where('slug', $record['slug'])
                ->first();

            if (!$product) {
                throw new Exception(
                    'Failed to find product with slug: \'' . $record['slug'] . '\'.'
                    . ' So product variant failed to be created: ' . json_encode($variant)
                );
            }

            $product->variants()
                ->firstOrCreate($variant);

            throw new SkipRecord();
        }
    }

    /**
     * Perform any additional actions after importing.
     *
     * @param array $record Is passed by reference
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function after(array &$record): void
    {
        /** @var Product|null $product */
        $product = Product::query()
            ->where('slug', $record['slug'])
            ->first();

        $metadata = $product->getJson('metadata');

        if ($metadata['menu_slug']) {
            /** @var Menu|null $menu */
            $menu = Menu::query()
                ->where('slug', $metadata['menu_slug'])
                ->firstOrFail();

            $menu->products()->attach($product->id);
        }

        if ($metadata['category_slug']) {
            /** @var Category|null $category */
            $category = Category::query()
                ->where('slug', $metadata['category_slug'])
                ->firstOrFail();

            $product->attachCategories($category);
        }
    }
}
