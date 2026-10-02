<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Helpers\WebCacheHelper;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

/**
 * Class MenuEditorTest.
 *
 * The editor's menus, and the order of menus and their categories.
 */
class MenuEditorTest extends EditorTestCase
{
    /**
     * @var DishMenu
     */
    protected DishMenu $menu;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)
            ->create(['title' => 'Main menu', 'popularity' => 5]);
    }

    /**
     * Test that a new menu goes at the end, with its texts in all languages.
     *
     * @return void
     */
    public function testMenuIsCreatedAtTheEnd()
    {
        $id = $this->postJson("/api/editor/restaurants/{$this->restaurant->id}/menus", [
            'title' => ['en' => 'Drinks', 'uk' => 'Напої'],
            'description' => ['en' => 'Coffee and tea'],
            'is_hidden' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', ['en' => 'Drinks', 'uk' => 'Напої'])
            ->assertJsonPath('data.description', ['en' => 'Coffee and tea', 'uk' => null])
            ->assertJsonPath('data.is_hidden', true)
            ->assertJsonPath('data.categories', [])
            ->json('data.id');

        $menu = DishMenu::query()->withoutGlobalScopes()->findOrFail($id);

        $this->assertSame($this->restaurant->id, $menu->restaurant_id);
        $this->assertSame(4, $menu->popularity);

        $this->postJson("/api/editor/restaurants/{$this->restaurant->id}/menus", [
            'title' => ['uk' => 'Без англійської'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title.en']);
    }

    /**
     * Test that the restaurant's default language decides which translation is required.
     *
     * @return void
     */
    public function testDefaultLanguageTextIsRequired()
    {
        $this->restaurant->update(['locale' => 'uk']);

        $this->postJson("/api/editor/restaurants/{$this->restaurant->id}/menus", [
            'title' => ['en' => 'Drinks'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title.uk']);
    }

    /**
     * Test that texts and visibility are updated, and what's left out stays as it is.
     *
     * @return void
     */
    public function testMenuIsUpdated()
    {
        $this->menu->update(['description' => 'All day']);

        $this->patchJson("/api/editor/menus/{$this->menu->id}", [
            'title' => ['en' => 'Main menu', 'uk' => 'Основне меню'],
            'is_hidden' => true,
        ])
            ->assertOk()
            ->assertJsonPath('data.title.uk', 'Основне меню')
            ->assertJsonPath('data.description.en', 'All day')
            ->assertJsonPath('data.is_hidden', true);

        $this->assertTrue($this->menu->fresh()->is_hidden);
    }

    /**
     * Test that a menu is deleted only from the archive, with everything inside it.
     *
     * @return void
     */
    public function testMenuIsDeletedOnlyFromTheArchive()
    {
        $category = DishCategory::factory()->withMenu($this->menu)->create();
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($category)->create();

        $this->deleteJson("/api/editor/menus/{$this->menu->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['archived']);

        $this->postJson("/api/editor/menus/{$this->menu->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.archived', true)
            ->assertJsonPath('data.categories.0.id', $category->id);

        $this->assertNotNull($this->menu->fresh()->archived_at);

        $this->postJson("/api/editor/menus/{$this->menu->id}/unarchive")
            ->assertOk()
            ->assertJsonPath('data.archived', false)
            ->assertJsonPath('data.archived_at', null);

        $this->postJson("/api/editor/menus/{$this->menu->id}/archive")->assertOk();

        $this->deleteJson("/api/editor/menus/{$this->menu->id}")->assertOk();

        $this->assertSoftDeleted($this->menu);
        $this->assertSoftDeleted($category);
        $this->assertSoftDeleted($dish);

        $this->patchJson("/api/editor/menus/{$this->menu->id}", ['is_hidden' => false])->assertNotFound();
    }

    /**
     * Test that menus and categories are ordered, and categories moved between menus take their dishes.
     *
     * @return void
     */
    public function testMenusAndCategoriesAreOrdered()
    {
        $drinks = DishMenu::factory()->withRestaurant($this->restaurant)->create(['popularity' => 1]);
        $soups = DishCategory::factory()->withMenu($this->menu)->create();
        $salads = DishCategory::factory()->withMenu($this->menu)->create();
        $coffee = DishCategory::factory()->withMenu($drinks)->create();
        $latte = Dish::factory()->withMenu($drinks)->withCategory($coffee)->create();

        $key = WebCacheHelper::menusKey($this->restaurant->id);
        Cache::put($key, 'cached');

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/menus/order", [
            'menus' => [
                ['id' => $drinks->id, 'categories' => []],
                ['id' => $this->menu->id, 'categories' => [$coffee->id, $salads->id, $soups->id]],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.menus.*.id', [$drinks->id, $this->menu->id])
            ->assertJsonPath('data.menus.1.categories.*.id', [$coffee->id, $salads->id, $soups->id]);

        $this->assertSame($this->menu->id, $coffee->fresh()->menu_id);
        $this->assertSame($this->menu->id, $latte->fresh()->menu_id);
        $this->assertFalse(Cache::has($key));
    }

    /**
     * Test that only the restaurant's menus and categories can be ordered.
     *
     * @return void
     */
    public function testOnlyTheRestaurantsMenusCanBeOrdered()
    {
        $foreignMenu = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();
        $foreignCategory = DishCategory::factory()->withMenu($foreignMenu)->create();

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/menus/order", [
            'menus' => [
                ['id' => $foreignMenu->id, 'categories' => []],
                ['id' => $this->menu->id, 'categories' => [$foreignCategory->id]],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['menus.0.id', 'menus.1.categories.0']);
    }

    /**
     * Test that a menu is copied with everything inside it: the copy is hidden, what's inside keeps its states.
     *
     * @return void
     */
    public function testMenuIsDuplicatedWithEverythingInside()
    {
        $this->menu->update(['title' => ['en' => 'Main menu', 'uk' => 'Основне меню'], 'slug' => 'main']);
        $soups = DishCategory::factory()->withMenu($this->menu)->create(['title' => 'Soups', 'popularity' => 2]);
        $old = DishCategory::factory()->withMenu($this->menu)->create(['archived' => true, 'popularity' => 1]);
        $borscht = Dish::factory()->withMenu($this->menu)->withCategory($soups)
            ->create(['title' => 'Borscht', 'price' => 185, 'popularity' => 2]);
        $hidden = Dish::factory()->withMenu($this->menu)->withCategory($soups)
            ->create(['is_hidden' => true, 'popularity' => 1]);
        DishVariant::factory()->withDish($borscht)->create(['price' => 245]);

        $response = $this->postJson("/api/editor/menus/{$this->menu->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.title', ['en' => 'Main menu', 'uk' => 'Основне меню'])
            ->assertJsonPath('data.slug', null)
            ->assertJsonPath('data.is_hidden', true)
            ->assertJsonPath('data.categories.*.archived', [false, true])
            ->assertJsonPath('data.categories.0.dishes.*.title.en', ['Borscht', $hidden->title])
            ->assertJsonPath('data.categories.0.dishes.*.is_hidden', [false, true])
            ->assertJsonPath('data.categories.0.dishes.0.sizes.1.price', 245);

        $copy = DishMenu::query()->withoutGlobalScopes()->findOrFail($response->json('data.id'));

        $this->assertSame(4, $copy->popularity);
        $this->assertNotContains($response->json('data.categories.0.id'), [$soups->id, $old->id]);
        $this->assertSame(2, $this->menu->categories()->withoutGlobalScopes()->count());
    }

    /**
     * Test that other restaurants' menus can't be changed.
     *
     * @return void
     */
    public function testOtherRestaurantsMenusCannotBeChanged()
    {
        $foreign = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();

        $this->patchJson("/api/editor/menus/{$foreign->id}", ['is_hidden' => true])->assertForbidden();
        $this->postJson("/api/editor/menus/{$foreign->id}/archive")->assertForbidden();
        $this->postJson("/api/editor/menus/{$foreign->id}/categories", ['title' => ['en' => 'Soups']])
            ->assertForbidden();

        Sanctum::actingAs($this->user(UserRole::Manager, $this->restaurant), ['*']);

        $this->postJson("/api/editor/menus/{$this->menu->id}/archive")->assertForbidden();
    }
}
