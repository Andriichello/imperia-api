<?php

namespace Tests\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use App\Models\User;
use Database\Factories\Morphs\MediaFactory;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class GuestVisibilityTest.
 *
 * Guests never see what's hidden, archived or deleted, at any level (menu, category, dish, size, photo),
 * on the page and through the API, and they see content in the page's language.
 */
class GuestVisibilityTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['locale' => 'en']);
    }

    /**
     * Page props of the restaurant in the language.
     *
     * @param string $locale
     * @param array $query
     *
     * @return array
     */
    protected function pageProps(string $locale = 'en', array $query = []): array
    {
        $response = $this->get(route('web.restaurant.preview', [
            'locale' => $locale,
            'restaurant_id' => $this->restaurant->id,
            ...$query,
        ]))->assertOk();

        return [
            'restaurant' => $response->viewData('restaurant')->resolve(),
            'menus' => $response->viewData('menus')->resolve(),
        ];
    }

    /**
     * Ids of the dishes the API gives for the menus.
     *
     * @param int[] $menuIds
     * @param array $query
     *
     * @return int[]
     */
    protected function dishIds(array $menuIds, array $query = []): array
    {
        return $this->getJson('/api/dishes?' . http_build_query([
            'filter' => ['menu_ids' => implode(',', $menuIds)],
            ...$query,
        ]))
            ->assertOk()
            ->json('data.*.id');
    }

    /**
     * Test that hidden and archived menus and categories aren't on the page, and categories are in their order.
     *
     * @return void
     */
    public function testPageLeavesOutHiddenAndArchivedMenusAndCategories()
    {
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['popularity' => 3]);
        DishMenu::factory()->withRestaurant($this->restaurant)->create(['is_hidden' => true]);
        DishMenu::factory()->withRestaurant($this->restaurant)->create(['archived' => true]);

        $second = DishCategory::factory()->withMenu($menu)->create(['popularity' => 1]);
        $first = DishCategory::factory()->withMenu($menu)->create(['popularity' => 2]);
        DishCategory::factory()->withMenu($menu)->create(['is_hidden' => true]);
        DishCategory::factory()->withMenu($menu)->create(['archived' => true]);

        $props = $this->pageProps();

        $this->assertSame([$menu->id], array_column($props['menus'], 'id'));
        $this->assertSame(
            [$first->id, $second->id],
            collect($props['menus'][0]['categories'])->pluck('id')->all()
        );
    }

    /**
     * Test that the dishes API leaves out dishes guests don't see, or whose menu or category they don't see.
     *
     * @return void
     */
    public function testDishesLeaveOutWhatGuestsDontSee()
    {
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $hiddenMenu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['is_hidden' => true]);
        $archivedMenu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['archived' => true]);

        $category = DishCategory::factory()->withMenu($menu)->create();
        $hiddenCategory = DishCategory::factory()->withMenu($menu)->create(['is_hidden' => true]);
        $archivedCategory = DishCategory::factory()->withMenu($menu)->create(['archived' => true]);

        $visible = Dish::factory()->withMenu($menu)->withCategory($category)->create();
        Dish::factory()->withMenu($menu)->withCategory($category)->create(['is_hidden' => true]);
        Dish::factory()->withMenu($menu)->withCategory($category)->create(['archived' => true]);
        Dish::factory()->withMenu($menu)->withCategory($hiddenCategory)->create();
        Dish::factory()->withMenu($menu)->withCategory($archivedCategory)->create();
        Dish::factory()->withMenu($menu)->create();

        foreach ([$hiddenMenu, $archivedMenu] as $other) {
            Dish::factory()->withMenu($other)
                ->withCategory(DishCategory::factory()->withMenu($other)->create())
                ->create();
        }

        $this->assertSame(
            [$visible->id],
            $this->dishIds([$menu->id, $hiddenMenu->id, $archivedMenu->id])
        );

        $this->getJson("/api/dishes/{$visible->id}")->assertOk();
        $this->getJson('/api/dishes/categories/' . $hiddenCategory->id)->assertNotFound();
    }

    /**
     * Test that hidden and archived sizes and hidden photos aren't shown to guests, neither on the
     * page nor through the API. A dish shows its first size guests see.
     *
     * @return void
     */
    public function testHiddenSizesAndPhotosArentShown()
    {
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $category = DishCategory::factory()->withMenu($menu)->create();
        $dish = Dish::factory()->withMenu($menu)->withCategory($category)->create(['price' => 100]);
        /** @var DishVariant $first */
        $first = $dish->sizes()->sole();
        DishVariant::factory()->withDish($dish)->create(['price' => 50, 'is_hidden' => true]);
        DishVariant::factory()->withDish($dish)->create(['price' => 60, 'archived' => true]);
        $large = DishVariant::factory()->withDish($dish)->create(['price' => 150]);

        [$shown, $hidden] = MediaFactory::new()->count(2)->create(['restaurant_id' => $this->restaurant->id]);
        $photos = [['id' => $hidden->id, 'is_hidden' => true], ['id' => $shown->id]];
        $dish->setMediaWithVisibility($photos);
        $this->restaurant->setMediaWithVisibility($photos);

        $dishes = $this->getJson('/api/dishes?' . http_build_query([
            'filter' => ['menu_ids' => $menu->id],
            'include' => 'variants,media',
        ]))->assertOk();

        $this->assertSame([$first->id, $large->id], $dishes->json('data.0.variants.*.id'));
        $this->assertEquals(100, $dishes->json('data.0.price'));
        $this->assertSame([$shown->id], $dishes->json('data.0.media.*.id'));

        $this->getJson("/api/dishes/{$dish->id}?include=variants,media")
            ->assertOk()
            ->assertJsonPath('data.variants.*.id', [$first->id, $large->id])
            ->assertJsonPath('data.media.*.id', [$shown->id]);

        $this->assertSame([$shown->id], collect($this->pageProps()['restaurant']['media'])->pluck('id')->all());
    }

    /**
     * Test that guests can't ask for deleted dishes, staff can.
     *
     * @return void
     */
    public function testOnlyStaffCanAskForDeletedDishes()
    {
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $category = DishCategory::factory()->withMenu($menu)->create();
        $deleted = Dish::factory()->withMenu($menu)->withCategory($category)->create();
        $deleted->delete();

        $this->assertSame([], $this->dishIds([$menu->id], ['deleted' => 'with']));

        $admin = User::factory()->withRole(UserRole::Admin())->create();
        Sanctum::actingAs($admin, ['*']);

        $this->assertSame([$deleted->id], $this->dishIds([$menu->id], ['deleted' => 'with']));
    }

    /**
     * Test that staff asking for archived menus on the page get the guests' page,
     * so the cached page never has them.
     *
     * @return void
     */
    public function testPageIsTheSameForStaffAskingForMore()
    {
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        DishMenu::factory()->withRestaurant($this->restaurant)->create(['archived' => true]);

        $admin = User::factory()->withRole(UserRole::Admin())->create();
        Sanctum::actingAs($admin, ['*']);

        $props = $this->pageProps('en', ['archived' => 'with', 'deleted' => 'with']);
        $this->assertSame([$menu->id], array_column($props['menus'], 'id'));

        $this->app['auth']->forgetGuards();

        $this->assertSame([$menu->id], array_column($this->pageProps()['menus'], 'id'));
    }

    /**
     * Test that content is shown in the page's language, or in another one when it has no translation.
     *
     * @return void
     */
    public function testContentIsInThePagesLanguage()
    {
        $this->restaurant->update(['name' => ['en' => 'Smak', 'uk' => 'Смак']]);
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)
            ->create(['title' => ['en' => 'Main menu', 'uk' => 'Основне меню'], 'description' => 'All day']);
        $category = DishCategory::factory()->withMenu($menu)->create();
        $dish = Dish::factory()->withMenu($menu)->withCategory($category)
            ->create(['title' => ['en' => 'Borscht', 'uk' => 'Борщ']]);

        $ukrainian = $this->pageProps('uk');
        $this->assertSame('Смак', $ukrainian['restaurant']['name']);
        $this->assertSame('Основне меню', $ukrainian['menus'][0]['title']);
        // no Ukrainian translation: the English one
        $this->assertSame('All day', $ukrainian['menus'][0]['description']);

        $this->assertSame('Main menu', $this->pageProps('en')['menus'][0]['title']);

        $this->getJson("/api/dishes/{$dish->id}", ['Accept-Language' => 'uk'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Борщ');
    }
}
