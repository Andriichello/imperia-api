<?php

namespace Tests\Jobs\Morph;

use App\Jobs\Morph\PerformAlternations;
use App\Models\Morphs\Alteration;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Restaurant;
use Exception;
use Tests\TestCase;

/**
 * Class PerformAlternationsTest.
 *
 * Scheduled changes of the old menu (products and their variants). Changes of menus,
 * categories, dishes and sizes are scheduled versions (see `ApplyDueVersionsTest`).
 */
class PerformAlternationsTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var Product
     */
    protected Product $product;

    /**
     * @var ProductVariant
     */
    protected ProductVariant $variant;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();
        $this->product = Product::factory()
            ->withRestaurant($this->restaurant)
            ->create(['title' => 'Borscht', 'price' => 100]);
        $this->variant = ProductVariant::factory()
            ->withProduct($this->product)
            ->create(['price' => 150]);
    }

    /**
     * Test that due alterations are applied to products.
     *
     * @return void
     * @throws Exception
     */
    public function testPerformsDueAlterationOnProduct()
    {
        $alteration = Alteration::factory()
            ->withModel($this->product)
            ->withValues(['price' => 120, 'title' => 'Beet soup'])
            ->performAt(now()->subMinute())
            ->create();

        (new PerformAlternations())->handle();

        $product = $this->product->fresh();
        $this->assertEquals(120, $product->price);
        $this->assertSame('Beet soup', $product->title);

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
            ->withModel($this->product)
            ->withValues(['price' => 120])
            ->performAt(now()->addDay())
            ->create();

        (new PerformAlternations())->handle();

        $this->assertEquals(100, $this->product->fresh()->price);
        $this->assertNull($alteration->fresh()->performed_at);
        $this->assertFalse($this->product->hasPendingAlterations());
    }

    /**
     * Test that a variant's price can be changed in advance.
     *
     * @return void
     * @throws Exception
     */
    public function testAltersProductVariant()
    {
        Alteration::factory()
            ->withModel($this->variant)
            ->withValues(['price' => 175])
            ->performAt(now()->subMinute())
            ->create();

        (new PerformAlternations())->handle();

        $this->assertEquals(175, $this->variant->fresh()->price);
    }

    /**
     * Test that the restaurant is filled in for alterations of the old menu's models.
     *
     * @return void
     */
    public function testFillsRestaurantId()
    {
        foreach ([$this->product, $this->variant] as $model) {
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
            ->create(['alterable_id' => 999999, 'alterable_type' => $this->product->getMorphClass()]);

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
