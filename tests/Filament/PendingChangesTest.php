<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Widgets\PendingChanges;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Morphs\Alteration;
use App\Models\Product;
use App\Models\Restaurant;
use Filament\Widgets\FilamentInfoWidget;
use Livewire\Livewire;

/**
 * Class PendingChangesTest.
 *
 * The dashboard widget with scheduled changes, which aren't performed yet.
 */
class PendingChangesTest extends FilamentTestCase
{
    /**
     * Test that the widget lists the pending changes of the user's restaurant,
     * failed ones first, without performed ones or changes of the old menu.
     *
     * @return void
     */
    public function testListsPendingChangesOfTheUsersRestaurant()
    {
        // without a timezone, like restaurants created before it was added
        $restaurant = Restaurant::factory()->create(['timezone' => '']);
        $menu = DishMenu::factory()->withRestaurant($restaurant)->create();
        $dish = Dish::factory()->withMenu($menu)->create();

        $scheduled = Alteration::factory()
            ->withModel($dish)
            ->withValues(['price' => 120])
            ->performAt(now()->addWeek())
            ->create();
        $failed = Alteration::factory()
            ->withModel($dish)
            ->withValues(['price' => 130])
            ->performAt(now()->subDay())
            ->create(['failed_at' => now(), 'exception' => 'Something went wrong']);
        $performed = Alteration::factory()
            ->withModel($dish)
            ->withValues(['price' => 110])
            ->performAt(now()->subWeek())
            ->create(['performed_at' => now()->subWeek()]);

        $otherMenu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $otherRestaurant = Alteration::factory()
            ->withModel(Dish::factory()->withMenu($otherMenu)->create())
            ->withValues(['price' => 1])
            ->performAt(now()->addDay())
            ->create();
        $oldMenu = Alteration::factory()
            ->withModel(Product::factory()->withRestaurant($restaurant)->create())
            ->withValues(['price' => 1])
            ->performAt(now()->addDay())
            ->create();

        $this->actingAsStaff(UserRole::Admin, $restaurant);

        Livewire::test(PendingChanges::class)
            ->assertCanSeeTableRecords([$failed, $scheduled], inOrder: true)
            ->assertCanNotSeeTableRecords([$performed, $otherRestaurant, $oldMenu]);
    }

    /**
     * Test that the dashboard shows the widget instead of the Filament info.
     *
     * @return void
     */
    public function testDashboardShowsTheWidget()
    {
        $this->actingAsStaff();

        $widgets = filament()->getPanel('admin')->getWidgets();

        $this->assertContains(PendingChanges::class, $widgets);
        $this->assertNotContains(FilamentInfoWidget::class, $widgets);

        $this->get('/admin')->assertOk();
    }
}
