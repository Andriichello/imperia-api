<?php

namespace Tests\Http\Controllers\Web;

use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\Schedule;
use App\Models\ScheduleException;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Class PreviewControllerTest.
 */
class PreviewControllerTest extends TestCase
{
    /**
     * Target restaurant.
     *
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create();
    }

    /**
     * Create a menu of the restaurant.
     *
     * @param array $attributes
     *
     * @return DishMenu
     */
    protected function createMenu(array $attributes = []): DishMenu
    {
        return DishMenu::factory()
            ->withRestaurant($this->restaurant)
            ->create($attributes);
    }

    /**
     * URL of the restaurant page.
     *
     * @return string
     */
    protected function restaurantUrl(): string
    {
        return route('web.restaurant.preview', [
            'locale' => 'en',
            'restaurant_id' => $this->restaurant->id,
        ]);
    }

    /**
     * URL of the restaurant's menu page.
     *
     * @param int|null $menuId
     *
     * @return string
     */
    protected function menuUrl(?int $menuId = null): string
    {
        return route('web.menu.preview', [
            'locale' => 'en',
            'restaurant_id' => $this->restaurant->id,
            'menu_id' => $menuId,
        ]);
    }

    /**
     * Test the restaurant page: it has the developer's contact for its footer (from the config).
     *
     * @return void
     */
    public function testRestaurantPage()
    {
        config(['app.developer_email' => 'developer@example.com']);

        $this->get($this->restaurantUrl())
            ->assertOk()
            ->assertViewIs('web.app')
            ->assertViewHas('developer_email', 'developer@example.com')
            ->assertSee('developer@example.com');

        $this->get(route('web.restaurant.preview', ['locale' => 'en', 'restaurant_id' => 999]))
            ->assertNotFound();
    }

    /**
     * Test the menu page.
     *
     * @return void
     */
    public function testMenuPage()
    {
        $menu = $this->createMenu();

        $this->get($this->menuUrl($menu->id))
            ->assertOk()
            ->assertViewIs('web.app');
    }

    /**
     * Test that the menu page without a menu opens the first (most popular) menu.
     *
     * @return void
     */
    public function testMenuPageWithoutMenuOpensTheFirstMenu()
    {
        $this->createMenu(['popularity' => 1]);
        $first = $this->createMenu(['popularity' => 10]);

        $this->get($this->menuUrl())
            ->assertRedirect($this->menuUrl($first->id));
    }

    /**
     * Test that a menu, which doesn't exist, is hidden or belongs to another
     * restaurant, opens the restaurant page.
     *
     * @return void
     */
    public function testUnknownMenuOpensTheRestaurantPage()
    {
        $this->createMenu();
        $hidden = $this->createMenu(['archived' => true]);
        $other = DishMenu::factory()
            ->withRestaurant(Restaurant::factory()->create())
            ->create();

        foreach ([999, $hidden->id, $other->id] as $menuId) {
            $this->get($this->menuUrl($menuId))
                ->assertRedirect($this->restaurantUrl());
        }
    }

    /**
     * Get the restaurant page (it caches the restaurant and its menus).
     *
     * @param string|null $idOrSlug
     *
     * @return TestResponse
     */
    protected function getRestaurantPage(?string $idOrSlug = null): TestResponse
    {
        return $this->get(route('web.restaurant.preview', [
            'locale' => 'en',
            'restaurant_id' => $idOrSlug ?? $this->restaurant->id,
        ]))->assertOk();
    }

    /**
     * Test that changes of the restaurant, its hours, menus and categories are
     * shown right away, without waiting for the cached page to expire.
     *
     * @return void
     */
    public function testChangesAreShownWithoutWaitingForTheCache()
    {
        $schedule = Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday('monday')
            ->create(['archived' => false]);
        $menu = $this->createMenu();

        // a day is closed
        $this->getRestaurantPage();
        $schedule->update(['archived' => true]);

        $restaurant = $this->getRestaurantPage()->viewData('restaurant')->resource;
        $this->assertTrue((bool) $restaurant->schedules->first()->archived);

        // a menu is renamed
        $menu->update(['title' => 'Renamed']);

        $menus = $this->getRestaurantPage()->viewData('menus')->collection;
        $this->assertSame('Renamed', $menus->first()->resource->title);

        // a category is added
        DishCategory::factory()->withMenu($menu)->create(['title' => 'Added']);

        $menus = $this->getRestaurantPage()->viewData('menus')->collection;
        $this->assertSame(['Added'], $menus->first()->resource->categories->pluck('title')->all());

        // the restaurant is renamed (its page is cached by slug too)
        $this->getRestaurantPage(strtoupper($this->restaurant->slug));
        $this->restaurant->update(['name' => 'Renamed']);

        $restaurant = $this->getRestaurantPage($this->restaurant->slug)->viewData('restaurant')->resource;
        $this->assertSame('Renamed', $restaurant->name);
    }

    /**
     * Test that the menu page of a restaurant without menus opens the restaurant page.
     *
     * @return void
     */
    public function testMenuPageWithoutMenusOpensTheRestaurantPage()
    {
        $this->get($this->menuUrl())
            ->assertRedirect($this->restaurantUrl());
    }

    /**
     * Test that the page has the restaurant's brand colors, its closure and the special days,
     * which haven't passed, in the page's language.
     *
     * @return void
     */
    public function testClosureSpecialDaysAndBrandColorsAreShown()
    {
        Carbon::setTestNow(Carbon::parse('2026-10-02 12:00', 'Europe/Kyiv'));

        $this->restaurant->fill([
            'timezone' => 'Europe/Kyiv',
            'brand_primary' => '#6db0bb',
            'brand_primary_content' => '#295a5a',
            'brand_accent' => '#4B858B',
            'closed_until' => '2026-10-14',
        ]);
        $this->restaurant->putTranslations('closed_reason', ['en' => 'Renovation', 'uk' => 'Ремонт']);
        $this->restaurant->save();

        $factory = ScheduleException::factory()->withRestaurant($this->restaurant);
        $factory->create(['starts_on' => '2026-09-20', 'ends_on' => '2026-09-30']);
        /** @var ScheduleException $yesterday */
        $yesterday = $factory->create(['starts_on' => '2026-10-01', 'ends_on' => '2026-10-01']);
        /** @var ScheduleException $christmas */
        $christmas = $factory->create([
            'starts_on' => '2026-12-24',
            'ends_on' => '2026-12-24',
            'is_closed' => false,
            'beg_hour' => 10,
            'beg_minute' => 0,
            'end_hour' => 18,
            'end_minute' => 0,
            'reason' => ['en' => 'Christmas Eve', 'uk' => 'Святвечір'],
        ]);

        $url = route('web.restaurant.preview', ['locale' => 'uk', 'restaurant_id' => $this->restaurant->id]);

        $response = $this->get($url)
            ->assertOk()
            ->assertSee(
                'style="--color-warning: #6db0bb; --color-warning-content: #295a5a; --color-accent: #4b858b;"',
                false
            );

        $restaurant = $response->viewData('restaurant')->resolve();

        $this->assertSame('2026-10-14', $restaurant['closed_until']);
        $this->assertSame('Ремонт', $restaurant['closed_reason']);
        $this->assertSame([$yesterday->id, $christmas->id], collect($restaurant['exceptions'])->pluck('id')->all());
        $this->assertSame('Святвечір', $restaurant['exceptions'][1]->resolve()['reason']);

        // without brand colors, the page has the default ones (and its accent)
        $this->restaurant->update(['brand_primary' => null, 'brand_primary_content' => null]);

        $this->getRestaurantPage()
            ->assertDontSee('--color-warning:', false)
            ->assertSee('style="--color-accent: #4b858b;"', false);

        // without an accent, the one of `app.css`
        $this->restaurant->update(['brand_accent' => null]);

        $this->getRestaurantPage()->assertDontSee('--color-accent:', false);
    }

    /**
     * Test that redirects keep the query, so the editor's preview stays one (`?editor=1`).
     *
     * @return void
     */
    public function testRedirectsKeepTheQuery()
    {
        $this->get($this->menuUrl() . '?editor=1')
            ->assertRedirect($this->restaurantUrl() . '?editor=1');

        $menu = $this->createMenu();

        $this->get($this->menuUrl() . '?editor=1&archived=with')
            ->assertRedirect($this->menuUrl($menu->id) . '?editor=1');
    }
}
