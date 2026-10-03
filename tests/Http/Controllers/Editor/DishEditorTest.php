<?php

namespace Tests\Http\Controllers\Editor;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use Database\Factories\Morphs\MediaFactory;

/**
 * Class DishEditorTest.
 *
 * The editor's dishes.
 */
class DishEditorTest extends EditorTestCase
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

        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $this->category = DishCategory::factory()->withMenu($this->menu)->create();
    }

    /**
     * Values of a new dish.
     *
     * @param array $overrides
     *
     * @return array
     */
    protected function dishData(array $overrides = []): array
    {
        return array_merge([
            'title' => ['en' => 'Ukrainian borscht', 'uk' => 'Український борщ'],
            'description' => ['en' => 'Beetroot soup with beef.'],
            'badge' => ['en' => 'Chef\'s recommendation'],
            'flags' => ['medium-hotness', 'alg-milk', 'alg-celery'],
            'sizes' => [
                ['price' => 185, 'weight' => 300, 'weight_unit' => 'g', 'calories' => 380, 'preparation_time' => 15],
                ['price' => 245, 'weight' => 450, 'weight_unit' => 'g', 'calories' => 520, 'preparation_time' => 15],
            ],
        ], $overrides);
    }

    /**
     * Test that a new dish goes at the end of its category, with its sizes (all of them variants)
     * and photos. Its own size columns are its first size.
     *
     * @return void
     */
    public function testDishIsCreatedWithItsSizes()
    {
        Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['popularity' => 3]);
        $photo = MediaFactory::new()->create(['restaurant_id' => $this->restaurant->id]);

        $id = $this->postJson(
            "/api/editor/categories/{$this->category->id}/dishes",
            $this->dishData(['media' => [['id' => $photo->id]], 'is_hidden' => true])
        )
            ->assertCreated()
            ->assertJsonPath('data.menu_id', $this->menu->id)
            ->assertJsonPath('data.category_id', $this->category->id)
            ->assertJsonPath('data.title.uk', 'Український борщ')
            ->assertJsonPath('data.badge', ['en' => 'Chef\'s recommendation', 'uk' => null])
            ->assertJsonPath('data.flags', ['medium-hotness', 'alg-milk', 'alg-celery'])
            ->assertJsonPath('data.is_hidden', true)
            ->assertJsonPath('data.sizes.*.price', [185, 245])
            ->assertJsonPath('data.sizes.0.weight', '300')
            ->assertJsonPath('data.sizes.*.is_hidden', [false, false])
            ->assertJsonPath('data.sizes.1.calories', 520)
            ->assertJsonPath('data.photos.0.id', $photo->id)
            ->json('data.id');

        $dish = Dish::query()->withoutGlobalScopes()->findOrFail($id);

        $this->assertEquals(185, $dish->price);
        $this->assertSame(2, $dish->popularity);
        $this->assertEquals([185, 245], $dish->variants->pluck('price')->all());
        // no extra size was made on the way
        $this->assertSame(2, DishVariant::query()->withoutGlobalScopes()->where('dish_id', $id)->count());
    }

    /**
     * Test that sizes are replaced: kept ones are updated (they keep their ids), others are deleted
     * or added, archived ones stay. The dish's own size columns follow its first size.
     *
     * @return void
     */
    public function testSizesAreReplaced()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)
            ->create(['price' => 100, 'weight' => '200', 'weight_unit' => 'g']);
        /** @var DishVariant $first */
        $first = $dish->sizes()->sole();
        $kept = DishVariant::factory()->withDish($dish)->create(['price' => 150]);
        $removed = DishVariant::factory()->withDish($dish)->create(['price' => 200]);
        $archived = DishVariant::factory()->withDish($dish)->create(['price' => 300, 'archived' => true]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", [
            'sizes' => [
                ['price' => 90, 'weight' => '0.5', 'weight_unit' => 'l'],
                ['id' => $kept->id, 'price' => 160, 'weight' => 1, 'weight_unit' => 'l', 'calories' => 300],
                ['price' => 210],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.sizes.*.price', [90, 160, 210])
            ->assertJsonPath('data.sizes.0.weight', '0.5')
            ->assertJsonPath('data.sizes.1.id', $kept->id)
            ->assertJsonPath('data.sizes.2.weight', null);

        $this->assertSoftDeleted($removed);
        $this->assertSoftDeleted($first);
        $this->assertNotSoftDeleted($archived);
        $this->assertEquals(160, $kept->fresh()->price);
        $this->assertEquals(90, $dish->fresh()->price);
        $this->assertSame('l', $dish->fresh()->weight_unit);

        // a single size: the variants are gone
        $this->patchJson("/api/editor/dishes/{$dish->id}", ['sizes' => [['price' => 120]]])
            ->assertOk()
            ->assertJsonPath('data.sizes.*.price', [120]);
    }

    /**
     * Test that the dish's values are checked.
     *
     * @return void
     */
    public function testDishIsValidated()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create();
        $foreignVariant = DishVariant::factory()
            ->withDish(Dish::factory()->withMenu($this->menu)->create())
            ->create();
        $foreignPhoto = MediaFactory::new()->create(['restaurant_id' => Restaurant::factory()->create()->id]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", [
            'title' => ['en' => ''],
            'badge' => ['en' => str_repeat('a', 26)],
            'flags' => ['spicy-as-hell'],
            'sizes' => [
                ['price' => -1, 'weight' => 300],
                ['id' => $foreignVariant->id, 'price' => 10, 'weight_unit' => 'bucket'],
            ],
            'media' => [['id' => $foreignPhoto->id]],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title.en',
                'badge.en',
                'flags.0',
                'sizes.0.price',
                'sizes.0.weight_unit',
                'sizes.1.id',
                'sizes.1.weight_unit',
                'media.0.id',
            ]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", ['sizes' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sizes']);

        $this->postJson("/api/editor/categories/{$this->category->id}/dishes", $this->dishData([
            'sizes' => [['id' => 1, 'price' => 10]],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sizes.0.id']);
    }

    /**
     * Test that a dish moves to a category of another menu, and its menu follows.
     *
     * @return void
     */
    public function testDishMovesToAnotherCategory()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create();
        $drinks = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $coffee = DishCategory::factory()->withMenu($drinks)->create();
        $foreign = DishCategory::factory()
            ->withMenu(DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create())
            ->create();

        $this->postJson("/api/editor/dishes/{$dish->id}/move", ['category_id' => $coffee->id])
            ->assertOk()
            ->assertJsonPath('data.menu_id', $drinks->id)
            ->assertJsonPath('data.category_id', $coffee->id);

        $this->postJson("/api/editor/dishes/{$dish->id}/move", ['category_id' => $foreign->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    }

    /**
     * Test that a dish is deleted only from the archive, and copies are hidden.
     *
     * @return void
     */
    public function testDishIsArchivedDuplicatedAndDeleted()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)
            ->create(['title' => ['en' => 'Borscht', 'uk' => 'Борщ'], 'slug' => 'borscht']);
        DishVariant::factory()->withDish($dish)->create();

        $this->postJson("/api/editor/dishes/{$dish->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.title', ['en' => 'Borscht', 'uk' => 'Борщ'])
            ->assertJsonPath('data.slug', null)
            ->assertJsonPath('data.is_hidden', true)
            ->assertJsonCount(2, 'data.sizes');

        $this->deleteJson("/api/editor/dishes/{$dish->id}")->assertUnprocessable();

        $this->postJson("/api/editor/dishes/{$dish->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.archived', true);

        $this->postJson("/api/editor/dishes/{$dish->id}/unarchive")
            ->assertOk()
            ->assertJsonPath('data.archived', false);

        $this->postJson("/api/editor/dishes/{$dish->id}/archive")->assertOk();
        $this->deleteJson("/api/editor/dishes/{$dish->id}")->assertOk();

        $this->assertSoftDeleted($dish);
    }

    /**
     * Test that a size can be hidden from guests, but a dish keeps a size they see.
     *
     * @return void
     */
    public function testSizesCanBeHiddenButOneStaysShown()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['price' => 100]);
        /** @var DishVariant $first */
        $first = $dish->sizes()->sole();
        $large = DishVariant::factory()->withDish($dish)->create(['price' => 150]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", [
            'sizes' => [
                ['id' => $first->id, 'price' => 100, 'is_hidden' => true],
                ['id' => $large->id, 'price' => 150],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.sizes.*.id', [$first->id, $large->id])
            ->assertJsonPath('data.sizes.*.is_hidden', [true, false]);

        // guests see the other size, which the dish shows now
        $this->assertSame([$large->id], $dish->variants()->pluck('id')->all());
        $this->assertEquals(150, $dish->fresh()->price);

        $this->patchJson("/api/editor/dishes/{$dish->id}", [
            'sizes' => [
                ['id' => $first->id, 'price' => 100, 'is_hidden' => true],
                ['id' => $large->id, 'price' => 150, 'is_hidden' => true],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sizes' => DishVariant::LAST_SIZE_MESSAGE]);

        $this->assertFalse($large->fresh()->is_hidden);
    }

    /**
     * Test that archived sizes are listed apart, and stay as they are when the sizes are saved.
     *
     * @return void
     */
    public function testArchivedSizesAreListedApart()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['price' => 100]);
        /** @var DishVariant $first */
        $first = $dish->sizes()->sole();
        $archived = DishVariant::factory()->withDish($dish)
            ->create(['price' => 245, 'archived' => true, 'archived_at' => now()]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", ['sizes' => [['id' => $first->id, 'price' => 110]]])
            ->assertOk()
            ->assertJsonPath('data.sizes.*.price', [110])
            ->assertJsonPath('data.archived_sizes.*.id', [$archived->id]);

        $this->assertNotSoftDeleted($archived);

        // an archived size can't be edited with the others
        $this->patchJson("/api/editor/dishes/{$dish->id}", ['sizes' => [['id' => $archived->id, 'price' => 1]]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sizes.0.id']);
    }

    /**
     * Test that photos of a dish can be hidden from guests, and a dish has 3 photos at most
     * (hidden ones count).
     *
     * @return void
     */
    public function testDishPhotosCanBeHiddenUpToThree()
    {
        $dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create();
        $photos = MediaFactory::new()->count(4)->create(['restaurant_id' => $this->restaurant->id]);

        $this->patchJson("/api/editor/dishes/{$dish->id}", [
            'media' => [
                ['id' => $photos[0]->id],
                ['id' => $photos[1]->id, 'is_hidden' => true],
                ['id' => $photos[2]->id],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.photos.*.id', [$photos[0]->id, $photos[1]->id, $photos[2]->id])
            ->assertJsonPath('data.photos.*.is_hidden', [false, true, false]);

        $this->assertSame([$photos[0]->id, $photos[2]->id], $dish->media()->pluck('media.id')->all());

        $this->patchJson("/api/editor/dishes/{$dish->id}", [
            'media' => $photos->map(fn ($photo) => ['id' => $photo->id])->all(),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['media']);
    }
}
