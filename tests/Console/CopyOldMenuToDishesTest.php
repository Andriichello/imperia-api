<?php

namespace Tests\Console;

use App\Enums\Hotness;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Menu;
use App\Models\Morphs\Alteration;
use App\Models\Morphs\Category;
use App\Models\Morphs\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Restaurant;
use Database\Factories\Morphs\MediaFactory;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class CopyOldMenuToDishesTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CopyOldMenuToDishesTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var Menu
     */
    protected Menu $kitchen;

    /**
     * @var Menu
     */
    protected Menu $lunch;

    /**
     * @var Category
     */
    protected Category $soups;

    /**
     * @var Product
     */
    protected Product $borscht;

    /**
     * @var Product
     */
    protected Product $uncategorized;

    /**
     * @var ProductVariant
     */
    protected ProductVariant $bigBorscht;

    /**
     * @var Media
     */
    protected Media $photo;

    /**
     * Setup the old menu structure.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();

        $this->kitchen = Menu::factory()->withRestaurant($this->restaurant)
            ->create(['title' => 'Kitchen', 'slug' => 'kitchen', 'popularity' => 10]);
        $this->lunch = Menu::factory()->withRestaurant($this->restaurant)
            ->create(['title' => 'Lunch', 'slug' => 'lunch', 'archived' => true]);

        $deletedMenu = Menu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Deleted']);
        $deletedMenu->delete();

        Menu::factory()->create(['title' => 'Nowhere', 'restaurant_id' => null]);

        $this->soups = Category::factory()->withRestaurant($this->restaurant)->withTarget('products')
            ->create(['title' => 'Soups', 'slug' => 'soups', 'popularity' => 5]);

        $this->borscht = Product::factory()->withRestaurant($this->restaurant)->create([
            'title' => 'Borscht',
            'price' => 150,
            'badge' => 'Hit',
        ]);
        $this->borscht->flags = ['high-protein'];
        $this->borscht->is_vegan = true;
        $this->borscht->has_nuts = true;
        $this->borscht->hotness = Hotness::Medium;
        $this->borscht->calories = 320;
        $this->borscht->preparation_time = 15;
        $this->borscht->save();
        $this->borscht->attachCategories($this->soups);

        $this->uncategorized = Product::factory()->withRestaurant($this->restaurant)
            ->create(['title' => 'Bread', 'archived' => true]);

        $deletedProduct = Product::factory()->withRestaurant($this->restaurant)->create(['title' => 'Gone']);
        $deletedProduct->delete();

        $this->kitchen->products()->attach([$this->borscht->id, $this->uncategorized->id, $deletedProduct->id]);
        $this->lunch->products()->attach([$this->borscht->id]);

        $this->bigBorscht = ProductVariant::factory()->withProduct($this->borscht)
            ->create(['price' => 200, 'weight' => '500', 'weight_unit' => 'g']);
        $deletedVariant = ProductVariant::factory()->withProduct($this->borscht)->create(['price' => 99]);
        $deletedVariant->delete();

        $this->photo = MediaFactory::new()->create();
        foreach ([$this->kitchen, $this->soups, $this->borscht] as $model) {
            DB::table('mediables')->insert([
                'media_id' => $this->photo->id,
                'mediable_id' => $model->getKey(),
                'mediable_type' => $model->getMorphClass(),
                'order' => 1,
            ]);
        }

        // pending changes are copied, done/failed ones aren't
        Alteration::factory()->withModel($this->borscht)->withValues(['price' => 175])
            ->performAt(now()->addWeek())->create();
        Alteration::factory()->withModel($this->borscht)->withValues(['price' => 1])
            ->create(['performed_at' => now()->subDay()]);
        Alteration::factory()->withModel($this->borscht)->withValues(['price' => 2])
            ->create(['failed_at' => now()->subDay()]);
        Alteration::factory()->withModel($this->bigBorscht)->withValues(['price' => 220])
            ->performAt(now()->addWeek())->create();
    }

    /**
     * Find the copy of an old product in the given old menu.
     *
     * @param Product $product
     * @param Menu $menu
     *
     * @return Dish
     */
    protected function dishOf(Product $product, Menu $menu): Dish
    {
        /** @var Dish $dish */
        $dish = Dish::query()->withoutGlobalScopes()->get()
            ->firstOrFail(function ($dish) use ($product, $menu) {
                /** @var Dish $dish */
                $source = $dish->getFromJson('metadata', 'copied_from');

                return $source['id'] === $product->id && $source['menu_id'] === $menu->id;
            });

        return $dish;
    }

    /**
     * Test the copied structure and values.
     *
     * @return void
     */
    public function testCopiesTheOldMenu()
    {
        $this->artisan('dishes:copy-old-menu')
            ->expectsOutputToContain('"Nowhere" has no restaurant')
            ->expectsOutputToContain('"Bread" in menu "Kitchen" has no category')
            ->assertSuccessful();

        // menus: kitchen + lunch (archived), not the deleted one or the one without a restaurant
        $menus = DishMenu::query()->withoutGlobalScopes()->orderBy('id')->get();
        $this->assertSame(['Kitchen', 'Lunch'], $menus->pluck('title')->all());
        $this->assertSame($this->restaurant->id, $menus[0]->restaurant_id);
        $this->assertSame('kitchen', $menus[0]->slug);
        $this->assertEquals(10, $menus[0]->popularity);
        $this->assertTrue((bool) $menus[1]->archived);

        // the soups category exists once per menu
        $categories = DishCategory::query()->withoutGlobalScopes()->get();
        $this->assertCount(2, $categories);
        $this->assertEqualsCanonicalizing($menus->pluck('id')->all(), $categories->pluck('menu_id')->all());

        // borscht is in both menus, bread (no category) only in the kitchen, the deleted product nowhere
        $this->assertSame(3, Dish::query()->withoutGlobalScopes()->count());

        $dish = $this->dishOf($this->borscht, $this->kitchen);
        $this->assertSame('Borscht', $dish->title);
        $this->assertEquals(150, $dish->price);
        $this->assertSame('Hit', $dish->badge);
        $this->assertEquals(320, $dish->calories);
        $this->assertEquals(15, $dish->preparation_time);
        $this->assertSame($menus[0]->id, $dish->menu_id);
        $this->assertSame('Soups', $dish->category->title);
        $this->assertEqualsCanonicalizing(
            ['high-protein', 'vegan', 'alg-nuts', 'medium-hotness'],
            $dish->flags
        );

        $bread = $this->dishOf($this->uncategorized, $this->kitchen);
        $this->assertNull($bread->category_id);
        $this->assertTrue((bool) $bread->archived);

        // variants: the deleted one isn't copied (per dish copy: 1 variant)
        $this->assertSame(2, DishVariant::query()->withoutGlobalScopes()->count());
        $this->assertEquals([200], DishVariant::query()->withoutGlobalScopes()
            ->where('dish_id', $dish->id)->pluck('price')->all());

        // images: the menu, the category (twice) and the dish (twice) share the same file
        $this->assertSame(5, DB::table('mediables')->where('media_id', $this->photo->id)
            ->whereIn('mediable_type', ['dish-menus', 'dish-categories', 'dishes'])->count());

        // scheduled changes: the pending dish change for both copies, the variant change for both variant copies
        $copied = Alteration::query()->whereIn('alterable_type', ['dishes', 'dish-variants'])->get();
        $this->assertCount(4, $copied);
        $this->assertSame([175], $copied->where('alterable_type', 'dishes')->pluck('metadata')
            ->map(fn ($metadata) => json_decode($metadata, true)['price'])->unique()->values()->all());
        $this->assertSame($this->restaurant->id, $copied->first()->restaurant_id);
    }

    /**
     * Test that a second run doesn't copy anything again, also not a deleted copy.
     *
     * @return void
     */
    public function testRunningAgainCopiesNothingNew()
    {
        $this->artisan('dishes:copy-old-menu')->assertSuccessful();

        $this->dishOf($this->borscht, $this->kitchen)->delete();

        $counts = fn () => [
            DishMenu::query()->withoutGlobalScopes()->count(),
            DishCategory::query()->withoutGlobalScopes()->count(),
            Dish::query()->withoutGlobalScopes()->count(),
            DishVariant::query()->withoutGlobalScopes()->count(),
            Alteration::query()->count(),
        ];
        $before = $counts();

        $this->artisan('dishes:copy-old-menu')
            ->expectsTable(['', 'Copied', 'Already copied'], [
                ['Menus', 0, 2],
                ['Categories', 0, 2],
                ['Dishes', 0, 3],
                ['Variants', 0, 0],
                ['Images', 0, 0],
                ['Scheduled changes', 0, 0],
            ])
            ->assertSuccessful();

        $this->assertSame($before, $counts());
        $this->assertTrue($this->dishOf($this->borscht, $this->kitchen)->trashed());
    }

    /**
     * Test that a dry run reports the counts, but saves nothing.
     *
     * @return void
     */
    public function testDryRunSavesNothing()
    {
        $this->artisan('dishes:copy-old-menu', ['--dry-run' => true])
            ->expectsTable(['', 'Would copy', 'Already copied'], [
                ['Menus', 2, 0],
                ['Categories', 2, 0],
                ['Dishes', 3, 0],
                ['Variants', 2, 0],
                ['Images', 5, 0],
                ['Scheduled changes', 4, 0],
            ])
            ->expectsOutputToContain('Dry run: nothing was saved.')
            ->assertSuccessful();

        $this->assertSame(0, DishMenu::query()->withoutGlobalScopes()->count());
        $this->assertSame(0, Dish::query()->withoutGlobalScopes()->count());
        $this->assertSame(0, DB::table('mediables')->where('mediable_type', 'dishes')->count());
    }

    /**
     * Test that the copy can be limited to one restaurant.
     *
     * @return void
     */
    public function testCopiesOnlyTheGivenRestaurant()
    {
        $other = Restaurant::factory()->create();
        Menu::factory()->withRestaurant($other)->create(['title' => 'Other']);

        $this->artisan('dishes:copy-old-menu', ['--restaurant' => $other->id])->assertSuccessful();

        $this->assertSame(['Other'], DishMenu::query()->withoutGlobalScopes()->pluck('title')->all());
    }
}
