<?php

namespace Tests\Jobs\Version;

use App\Jobs\Version\ApplyDueVersions;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Repositories\Editor\VersionApplier;
use Carbon\CarbonInterface;
use Tests\TestCase;

/**
 * Class ApplyDueVersionsTest.
 *
 * Scheduled versions go live at their time, each in one go: one, which can't, fails
 * without stopping the others.
 */
class ApplyDueVersionsTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * The only size of a dish.
     *
     * @var DishVariant
     */
    protected DishVariant $size;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);
        $dish = Dish::factory()
            ->withMenu(DishMenu::factory()->withRestaurant($this->restaurant)->create())
            ->create(['price' => 100]);

        /** @var DishVariant $size */
        $size = $dish->sizes()->sole();
        $this->size = $size;
    }

    /**
     * A version, which changes the price of the size.
     *
     * @param float $price
     * @param string $status of the version
     * @param CarbonInterface $goesLiveAt
     *
     * @return MenuVersion
     */
    protected function priceChange(float $price, string $status, CarbonInterface $goesLiveAt): MenuVersion
    {
        $attributes = ['status' => $status, 'goes_live_at' => $goesLiveAt];
        $version = MenuVersion::factory()->withRestaurant($this->restaurant)->create($attributes);
        MenuVersionChange::factory()->inVersion($version)->changing($this->size, ['price' => $price])->create();

        return $version;
    }

    /**
     * Test that only scheduled versions, whose time has come, are applied, from the earliest.
     *
     * @return void
     */
    public function testAppliesScheduledVersionsWhoseTimeHasCome()
    {
        $earlier = $this->priceChange(110, MenuVersion::STATUS_SCHEDULED, now()->subHour());
        $later = $this->priceChange(120, MenuVersion::STATUS_SCHEDULED, now()->subMinute());
        $future = $this->priceChange(130, MenuVersion::STATUS_SCHEDULED, now()->addDay());
        $inactive = $this->priceChange(140, MenuVersion::STATUS_INACTIVE, now()->subDay());
        $draft = $this->priceChange(150, MenuVersion::STATUS_DRAFT, now()->subDay());

        (new ApplyDueVersions())->handle(app(VersionApplier::class));

        $this->assertEquals(120, $this->size->fresh()->price);
        $this->assertSame(MenuVersion::STATUS_APPLIED, $earlier->fresh()->status);
        $this->assertSame(MenuVersion::STATUS_APPLIED, $later->fresh()->status);
        $this->assertSame(MenuVersion::STATUS_SCHEDULED, $future->fresh()->status);
        $this->assertSame(MenuVersion::STATUS_INACTIVE, $inactive->fresh()->status);
        $this->assertSame(MenuVersion::STATUS_DRAFT, $draft->fresh()->status);
    }

    /**
     * Test that a version, which can't be applied, is failed (with nothing of it applied), the
     * others are applied, and it isn't tried again.
     *
     * @return void
     */
    public function testFailedVersionDoesntStopTheOthers()
    {
        $other = Dish::factory()->withMenu($this->size->dish->menu)->create();
        $broken = $this->priceChange(110, MenuVersion::STATUS_SCHEDULED, now()->subHour());
        MenuVersionChange::factory()->inVersion($broken)->changing($other, ['is_hidden' => true])->create();
        $other->delete();

        $fine = MenuVersion::factory()->withRestaurant($this->restaurant)
            ->create(['status' => MenuVersion::STATUS_SCHEDULED, 'goes_live_at' => now()->subMinute()]);
        MenuVersionChange::factory()->inVersion($fine)->changing($this->size, ['calories' => 300])->create();

        $this->artisan('versions:apply-due')
            ->expectsOutput('Applied 1, failed 1.')
            ->assertFailed();

        $size = $this->size->fresh();
        $this->assertEquals(100, $size->price);
        $this->assertSame(300, $size->calories);
        $this->assertSame(MenuVersion::STATUS_FAILED, $broken->fresh()->status);
        $this->assertSame('A changed dish doesn\'t exist anymore.', $broken->fresh()->failure_reason);

        $this->artisan('versions:apply-due')
            ->expectsOutput('Applied 0, failed 0.')
            ->assertSuccessful();
    }
}
