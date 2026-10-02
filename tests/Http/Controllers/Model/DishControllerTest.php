<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\UserRole;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;
use Tests\RegisteringTestCase;

/**
 * Class DishControllerTest.
 */
class DishControllerTest extends RegisteringTestCase
{
    /**
     * A dish shown on the website.
     *
     * @var Dish
     */
    protected Dish $live;

    /**
     * An archived (hidden) dish.
     *
     * @var Dish
     */
    protected Dish $archived;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $menu = DishMenu::factory()
            ->withRestaurant(Restaurant::factory()->create())
            ->create();

        $category = DishCategory::factory()
            ->withMenu($menu)
            ->create();

        $this->live = Dish::factory()
            ->withMenu($menu)
            ->withCategory($category)
            ->create();

        $this->archived = Dish::factory()
            ->withMenu($menu)
            ->withCategory($category)
            ->create(['archived' => true]);
    }

    /**
     * Titles of the dishes listed by the API.
     *
     * @param string $query
     *
     * @return array
     */
    protected function listedTitles(string $query = ''): array
    {
        return $this->getJson('/api/dishes' . $query)
            ->assertOk()
            ->json('data.*.title');
    }

    /**
     * Test that guests can't list archived dishes.
     *
     * @return void
     */
    public function testGuestsCantListArchivedDishes()
    {
        foreach (['', '?archived=only', '?archived=with'] as $query) {
            $this->assertEquals([$this->live->title], $this->listedTitles($query), $query);
        }
    }

    /**
     * Test that customers can't list archived dishes.
     *
     * @return void
     */
    public function testCustomersCantListArchivedDishes()
    {
        $this->login($this->registerFormData, UserRole::Customer);

        foreach (['', '?archived=only', '?archived=with'] as $query) {
            $this->assertEquals([$this->live->title], $this->listedTitles($query), $query);
        }
    }

    /**
     * Test that staff can list archived dishes.
     *
     * @return void
     */
    public function testStaffCanListArchivedDishes()
    {
        $this->login($this->registerFormData, UserRole::Manager);

        $this->assertEquals([$this->live->title], $this->listedTitles());
        $this->assertEquals([$this->archived->title], $this->listedTitles('?archived=only'));
        $this->assertEqualsCanonicalizing(
            [$this->live->title, $this->archived->title],
            $this->listedTitles('?archived=with')
        );
    }
}
