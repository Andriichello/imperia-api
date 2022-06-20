<?php

namespace Tests\Queries;

use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class ProductQueryBuilderTest.
 */
class ProductQueryBuilderTest extends TestCase
{
    public Collection $menus;

    public Collection $products;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->menus = collect();
        $this->products = collect();

        $menu = Menu::factory()->create();
        $products = Product::factory()
            ->withMenu($menu)
            ->count(2)
            ->create();

        $this->menus->push($menu);
        $this->products->push(...$products->all());

        $menu = Menu::factory()->create();
        $products = Product::factory()
            ->withMenu($menu)
            ->count(2)
            ->create();

        $this->menus->push($menu);
        $this->products->push(...$products->all());
    }

    /**
     * @return void
     */
    public function testCategorizable()
    {
        /** @var Product $product */
        $product = $this->products->get(0);
        $one = Category::factory()->create();
        $product->attachCategories($one);

        $products = Product::query()
            ->withAllOfCategories($one);

        $this->assertEquals(1, $products->count());
        $this->assertEquals($product->id, data_get($products->first(), 'id'));

        /** @var Product $product */
        $product = $this->products->get(1);
        $two = Category::factory()->create();
        $product->attachCategories($two);

        $products = Product::query()
            ->withAllOfCategories($two);

        $this->assertEquals(1, $products->count());
        $this->assertEquals($product->id, data_get($products->first(), 'id'));

        /** @var Product $product */
        $product = $this->products->get(2);
        $product->attachCategories($one, $two);

        $products = Product::query()
            ->withAllOfCategories($one, $two);

        $this->assertEquals(1, $products->count());
        $this->assertEquals($product->id, data_get($products->first(), 'id'));

        $products = Product::query()
            ->withAnyOfCategories($one, $two);

        $this->assertEquals(3, $products->count());
    }
}
