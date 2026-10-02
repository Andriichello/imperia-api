<?php

namespace Tests\Http\Controllers\Web;

use App\Models\DishMenu;
use App\Models\Restaurant;
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
     * Test the restaurant page.
     *
     * @return void
     */
    public function testRestaurantPage()
    {
        $this->get($this->restaurantUrl())
            ->assertOk()
            ->assertViewIs('web.app');

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
     * Test that the menu page of a restaurant without menus opens the restaurant page.
     *
     * @return void
     */
    public function testMenuPageWithoutMenusOpensTheRestaurantPage()
    {
        $this->get($this->menuUrl())
            ->assertRedirect($this->restaurantUrl());
    }
}
