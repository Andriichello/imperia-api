<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Widgets\PendingChanges;
use App\Models\MenuVersion;
use App\Models\Restaurant;
use Filament\Widgets\FilamentInfoWidget;
use Livewire\Livewire;

/**
 * Class PendingChangesTest.
 *
 * The dashboard widget with scheduled changes, which haven't gone live yet.
 */
class PendingChangesTest extends FilamentTestCase
{
    /**
     * Test that the widget lists the versions of the user's restaurant, which haven't gone live
     * (failed ones included), from the earliest, without applied ones or other restaurants' ones.
     *
     * @return void
     */
    public function testListsPendingChangesOfTheUsersRestaurant()
    {
        // without a timezone, like restaurants created before it was added
        $restaurant = Restaurant::factory()->create(['timezone' => '']);
        $versions = MenuVersion::factory()->withRestaurant($restaurant);

        $scheduled = $versions->scheduled(now()->addWeek())->create();
        $failed = $versions->create([
            'status' => MenuVersion::STATUS_FAILED,
            'goes_live_at' => now()->subDay(),
            'failure_reason' => 'A changed dish doesn\'t exist anymore.',
        ]);
        $applied = $versions->create([
            'status' => MenuVersion::STATUS_APPLIED,
            'goes_live_at' => now()->subWeek(),
            'applied_at' => now()->subWeek(),
        ]);
        $otherRestaurant = MenuVersion::factory()
            ->withRestaurant(Restaurant::factory()->create())
            ->scheduled(now()->addDay())
            ->create();

        $this->actingAsStaff(UserRole::Admin, $restaurant);

        Livewire::test(PendingChanges::class)
            ->assertCanSeeTableRecords([$failed, $scheduled], inOrder: true)
            ->assertCanNotSeeTableRecords([$applied, $otherRestaurant]);
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
