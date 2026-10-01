<?php

namespace Tests\Jobs\Morph;

use App\Jobs\Morph\PerformAlternations;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Morphs\Alteration;
use App\Models\Restaurant;
use Exception;
use Tests\TestCase;

/**
 * Class PerformAlternationsTest.
 */
class PerformAlternationsTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var DishMenu
     */
    protected DishMenu $menu;

    /**
     * @var DishCategory
     */
    protected DishCategory $category;

    /**
     * @var Dish
     */
    protected Dish $dish;

    /**
     * @var DishVariant
     */
    protected DishVariant $variant;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();
        $this->menu = DishMenu::factory()
            ->withRestaurant($this->restaurant)
            ->create(['title' => 'Kitchen']);
        $this->category = DishCategory::factory()
            ->withMenu($this->menu)
            ->create(['title' => 'Soups']);
        $this->dish = Dish::factory()
            ->withMenu($this->menu)
            ->withCategory($this->category)
            ->create(['price' => 100, 'flags' => ['vegetarian']]);
        $this->variant = DishVariant::factory()
            ->withDish($this->dish)
            ->create(['price' => 150]);
    }

    /**
     * Test that due alterations are applied to dishes.
     *
     * @return void
     * @throws Exception
     */
    public function testPerformsDueAlterationOnDish()
    {
        $alteration = Alteration::factory()
            ->withModel($this->dish)
            ->withValues(['price' => 120, 'flags' => ['vegan', 'alg-nuts']])
            ->performAt(now()->subMinute())
            ->create();

        (new PerformAlternations())->handle();

        $dish = $this->dish->fresh();
        $this->assertEquals(120, $dish->price);
        $this->assertSame(['vegan', 'alg-nuts'], $dish->flags);

        $alteration = $alteration->fresh();
        $this->assertNotNull($alteration->performed_at);
        $this->assertNull($alteration->failed_at);
    }

    /**
     * Test that alterations scheduled for the future are not applied yet.
     *
     * @return void
     * @throws Exception
     */
    public function testSkipsFutureAlterations()
    {
        $alteration = Alteration::factory()
            ->withModel($this->dish)
            ->withValues(['price' => 120])
            ->performAt(now()->addDay())
            ->create();

        (new PerformAlternations())->handle();

        $this->assertEquals(100, $this->dish->fresh()->price);
        $this->assertNull($alteration->fresh()->performed_at);
        $this->assertTrue($this->dish->hasPendingAlterations() === false);
    }

    /**
     * Test that a dish variant can be archived and its price changed in advance.
     *
     * @return void
     * @throws Exception
     */
    public function testArchivesDishVariant()
    {
        Alteration::factory()
            ->withModel($this->variant)
            ->withValues(['archived' => true, 'price' => 175])
            ->performAt(now()->subMinute())
            ->create();

        (new PerformAlternations())->handle();

        /** @var DishVariant $variant */
        $variant = DishVariant::query()
            ->withoutGlobalScopes()
            ->findOrFail($this->variant->id);

        $this->assertTrue((bool) $variant->archived);
        $this->assertEquals(175, $variant->price);
        $this->assertTrue($this->dish->fresh()->variants->isEmpty());
    }

    /**
     * Test that categories and menus can be altered.
     *
     * @return void
     * @throws Exception
     */
    public function testAltersDishCategoryAndMenu()
    {
        Alteration::factory()
            ->withModel($this->category)
            ->withValues(['title' => 'Hot soups'])
            ->performAt(now()->subMinute())
            ->create();

        Alteration::factory()
            ->withModel($this->menu)
            ->withValues(['title' => 'Summer kitchen', 'archived' => true])
            ->performAt(now()->subMinute())
            ->create();

        (new PerformAlternations())->handle();

        $this->assertSame('Hot soups', $this->category->fresh()->title);

        /** @var DishMenu $menu */
        $menu = DishMenu::query()
            ->withoutGlobalScopes()
            ->findOrFail($this->menu->id);

        $this->assertSame('Summer kitchen', $menu->title);
        $this->assertTrue((bool) $menu->archived);
    }

    /**
     * Test that the restaurant is filled in for alterations of all dish models.
     *
     * @return void
     */
    public function testFillsRestaurantId()
    {
        foreach ([$this->menu, $this->category, $this->dish, $this->variant] as $model) {
            $alteration = Alteration::factory()
                ->withModel($model)
                ->create();

            $this->assertSame($this->restaurant->id, $alteration->restaurant_id, $model::class);
        }
    }

    /**
     * Test that a failed alteration is marked as failed and isn't retried.
     *
     * @return void
     * @throws Exception
     */
    public function testFailedAlterationIsNotRetried()
    {
        $alteration = Alteration::factory()
            ->withValues(['price' => 120])
            ->performAt(now()->subMinute())
            ->create(['alterable_id' => 999999, 'alterable_type' => $this->dish->getMorphClass()]);

        try {
            (new PerformAlternations())->handle();
            $this->fail('Expected the job to report the failed alteration.');
        } catch (Exception $exception) {
            $this->assertStringContainsString((string) $alteration->id, $exception->getMessage());
        }

        $alteration = $alteration->fresh();
        $this->assertNull($alteration->performed_at);
        $this->assertNotNull($alteration->failed_at);
        $this->assertStringContainsString("doesn't exist", $alteration->exception);

        // the next run skips it instead of failing again
        (new PerformAlternations())->handle();

        $this->assertEquals($alteration->failed_at, $alteration->fresh()->failed_at);
    }
}
