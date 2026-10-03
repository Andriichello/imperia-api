<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\MenuVersion;
use App\Models\Restaurant;
use App\Models\Schedule;
use App\Models\ScheduleException;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

/**
 * Class DashboardEditorTest.
 *
 * What the admin's dashboard shows of a restaurant, and when its page was last saved.
 */
class DashboardEditorTest extends EditorTestCase
{
    /**
     * Test that the dashboard has what guests see, the hours, the upcoming special days and
     * the versions, which haven't gone live.
     *
     * @return void
     */
    public function testDashboardHasWhatTheRestaurantNeeds()
    {
        Carbon::setTestNow('2026-10-03 12:00:00');

        $main = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        DishMenu::factory()->withRestaurant($this->restaurant)->create(['is_hidden' => true]);
        DishMenu::factory()->withRestaurant($this->restaurant)->create(['archived' => true]);
        $soups = DishCategory::factory()->withMenu($main)->create();
        $hidden = DishCategory::factory()->withMenu($main)->create(['is_hidden' => true]);
        Dish::factory()->withMenu($main)->withCategory($soups)->count(2)->create();
        Dish::factory()->withMenu($main)->withCategory($soups)->create(['is_hidden' => true]);
        Dish::factory()->withMenu($main)->withCategory($hidden)->create();

        Schedule::factory()->withRestaurant($this->restaurant)->withWeekday('saturday')
            ->create(['beg_hour' => 10, 'beg_minute' => 0, 'end_hour' => 23, 'end_minute' => 0]);
        // over, so it isn't listed
        ScheduleException::factory()->withRestaurant($this->restaurant)
            ->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-01']);
        /** @var ScheduleException $today */
        $today = ScheduleException::factory()->withRestaurant($this->restaurant)
            ->create(['starts_on' => '2026-10-02', 'ends_on' => '2026-10-03']);
        /** @var ScheduleException $christmas */
        $christmas = ScheduleException::factory()->withRestaurant($this->restaurant)
            ->create(['starts_on' => '2026-12-25', 'ends_on' => '2026-12-25', 'reason' => 'Christmas Day']);

        $scheduled = MenuVersion::factory()->withRestaurant($this->restaurant)->scheduled()->create();
        MenuVersion::factory()->withRestaurant($this->restaurant)->create(['status' => MenuVersion::STATUS_APPLIED]);

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/dashboard")
            ->assertOk()
            ->assertJsonPath('data.name', ['en' => 'Smak', 'uk' => null])
            ->assertJsonPath('data.timezone', 'Europe/Kyiv')
            ->assertJsonPath('data.supported_locales', ['en', 'uk'])
            ->assertJsonPath('data.menus_count', 1)
            ->assertJsonPath('data.dishes_count', 2)
            ->assertJsonPath('data.weekdays.saturday.0.end_hour', 23)
            ->assertJsonPath('data.exceptions.*.id', [$today->id, $christmas->id])
            ->assertJsonPath('data.exceptions.1.reason.en', 'Christmas Day')
            ->assertJsonPath('data.versions.*.id', [$scheduled->id]);
    }

    /**
     * Test that saving the restaurant's page remembers when and by whom, and the dashboard shows it.
     *
     * @return void
     */
    public function testLastSaveIsRemembered()
    {
        Carbon::setTestNow('2026-10-03 11:32:00');

        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $category = DishCategory::factory()->withMenu($menu)->create();
        $dish = Dish::factory()->withMenu($menu)->withCategory($category)->create();
        Restaurant::query()->whereKey($this->restaurant->id)
            ->update(['last_saved_at' => null, 'last_saved_by' => null]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", ['is_hidden' => true])->assertOk();

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/dashboard")
            ->assertOk()
            // in the restaurant's time zone
            ->assertJsonPath('data.last_saved_at', '2026-10-03T14:32:00+03:00')
            ->assertJsonPath('data.last_saved_by', ['id' => $this->admin->id, 'name' => $this->admin->name]);

        // orders and photos fire no events, they're saves too
        Carbon::setTestNow('2026-10-03 11:40:00');

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/menus/order", [
            'menus' => [['id' => $menu->id, 'categories' => []]],
        ])->assertOk();

        $this->assertSame('2026-10-03 11:40:00', $this->restaurant->fresh()->last_saved_at->toDateTimeString());
    }

    /**
     * Test that only the restaurant's admins see its dashboard.
     *
     * @return void
     */
    public function testOnlyTheRestaurantsAdminsSeeTheDashboard()
    {
        Sanctum::actingAs($this->user(UserRole::Admin, Restaurant::factory()->create()), ['*']);

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/dashboard")->assertForbidden();

        Sanctum::actingAs($this->user(UserRole::Manager, $this->restaurant), ['*']);

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/dashboard")->assertForbidden();
    }
}
