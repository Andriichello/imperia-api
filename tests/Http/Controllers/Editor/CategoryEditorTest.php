<?php

namespace Tests\Http\Controllers\Editor;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\Restaurant;

/**
 * Class CategoryEditorTest.
 *
 * The editor's categories.
 */
class CategoryEditorTest extends EditorTestCase
{
    /**
     * @var DishMenu
     */
    protected DishMenu $menu;

    /**
     * @var DishCategory
     */
    protected DishCategory $category;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Main menu']);
        $this->category = DishCategory::factory()->withMenu($this->menu)
            ->create(['title' => 'Soups', 'popularity' => 3]);
    }

    /**
     * Test that a new category goes at the end of its menu.
     *
     * @return void
     */
    public function testCategoryIsCreatedAtTheEnd()
    {
        $id = $this->postJson("/api/editor/menus/{$this->menu->id}/categories", [
            'title' => ['en' => 'Salads', 'uk' => 'Салати'],
            'description' => ['en' => '', 'uk' => ''],
        ])
            ->assertCreated()
            ->assertJsonPath('data.menu_id', $this->menu->id)
            ->assertJsonPath('data.title', ['en' => 'Salads', 'uk' => 'Салати'])
            ->assertJsonPath('data.description', ['en' => null, 'uk' => null])
            ->assertJsonPath('data.is_hidden', false)
            ->json('data.id');

        $this->assertSame(2, DishCategory::query()->findOrFail($id)->popularity);
    }

    /**
     * Test that texts, visibility and the order of dishes are updated.
     *
     * @return void
     */
    public function testCategoryIsUpdatedWithTheOrderOfItsDishes()
    {
        $borscht = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['popularity' => 2]);
        $broth = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['popularity' => 1]);
        $other = Dish::factory()->withMenu($this->menu)->create();

        $this->patchJson("/api/editor/categories/{$this->category->id}", [
            'description' => ['en' => 'Served with bread', 'uk' => 'Подаємо з хлібом'],
            'is_hidden' => true,
            'dishes' => [$broth->id, $borscht->id],
        ])
            ->assertOk()
            ->assertJsonPath('data.title.en', 'Soups')
            ->assertJsonPath('data.description.uk', 'Подаємо з хлібом')
            ->assertJsonPath('data.is_hidden', true)
            ->assertJsonPath('data.dishes.*.id', [$broth->id, $borscht->id]);

        $this->patchJson("/api/editor/categories/{$this->category->id}", ['dishes' => [$other->id]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['dishes.0']);
    }

    /**
     * Test that a category moves to another menu of the restaurant with its dishes.
     *
     * @return void
     */
    public function testCategoryMovesWithItsDishes()
    {
        $drinks = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        DishCategory::factory()->withMenu($drinks)->create(['popularity' => 7]);
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['is_hidden' => true]);
        $foreign = DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create();

        $this->postJson("/api/editor/categories/{$this->category->id}/move", ['menu_id' => $drinks->id])
            ->assertOk()
            ->assertJsonPath('data.menu_id', $drinks->id)
            ->assertJsonPath('data.popularity', 6)
            ->assertJsonPath('data.dishes.0.menu_id', $drinks->id);

        $this->assertSame($drinks->id, $dish->fresh()->menu_id);

        $this->postJson("/api/editor/categories/{$this->category->id}/move", ['menu_id' => $foreign->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['menu_id']);
    }

    /**
     * Test that a category is deleted only from the archive, with its dishes.
     *
     * @return void
     */
    public function testCategoryIsDeletedOnlyFromTheArchive()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create();

        $this->deleteJson("/api/editor/categories/{$this->category->id}")->assertUnprocessable();

        $this->postJson("/api/editor/categories/{$this->category->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.archived', true);

        $this->deleteJson("/api/editor/categories/{$this->category->id}")->assertOk();

        $this->assertSoftDeleted($this->category);
        $this->assertSoftDeleted($dish);
    }

    /**
     * Test that a category is copied with its dishes, the copy is hidden.
     *
     * @return void
     */
    public function testCategoryIsDuplicated()
    {
        Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['title' => 'Borscht']);

        $id = $this->postJson("/api/editor/categories/{$this->category->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.title.en', 'Soups')
            ->assertJsonPath('data.is_hidden', true)
            ->assertJsonPath('data.popularity', 2)
            ->assertJsonPath('data.dishes.*.title.en', ['Borscht'])
            ->assertJsonPath('data.dishes.0.is_hidden', false)
            ->json('data.id');

        $this->assertNotSame($this->category->id, $id);
        $this->assertSame(1, $this->category->dishes()->count());
    }
}
