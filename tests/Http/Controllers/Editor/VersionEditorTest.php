<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use Carbon\Carbon;
use Database\Factories\Morphs\MediaFactory;
use Laravel\Sanctum\Sanctum;

/**
 * Class VersionEditorTest.
 *
 * Scheduled versions: changes, which go live together. Nothing reaches guests until a version
 * is applied, then all of it or nothing does.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class VersionEditorTest extends EditorTestCase
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
     * @var Dish
     */
    protected Dish $dish;

    /**
     * The dish's only size.
     *
     * @var DishVariant
     */
    protected DishVariant $size;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Main menu']);
        $this->category = DishCategory::factory()->withMenu($this->menu)->create(['title' => 'Soups']);
        $this->dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create([
            'title' => 'Chicken broth',
            'price' => 140,
            'weight' => '350',
            'weight_unit' => 'g',
        ]);

        /** @var DishVariant $size */
        $size = $this->dish->sizes()->sole();
        $this->size = $size;
    }

    /**
     * A draft version of the restaurant.
     *
     * @param array $attributes
     *
     * @return MenuVersion
     */
    protected function draft(array $attributes = []): MenuVersion
    {
        return MenuVersion::factory()
            ->withRestaurant($this->restaurant)
            ->createdBy($this->admin)
            ->create($attributes);
    }

    /**
     * Put a change into the version.
     *
     * @param MenuVersion $version
     * @param array $data
     *
     * @return mixed
     */
    protected function putChange(MenuVersion $version, array $data): mixed
    {
        return $this->putJson("/api/editor/versions/{$version->id}/changes", $data);
    }

    /**
     * Test that a version is created with its changes and scheduled right away: its time is
     * in the restaurant's time zone, nothing changes until it goes live.
     *
     * @return void
     */
    public function testVersionIsCreatedWithItsChanges()
    {
        $this->postJson("/api/editor/restaurants/{$this->restaurant->id}/versions", [
            'name' => 'Winter menu',
            'goes_live_at' => '2030-11-01 00:00',
            'schedule' => true,
            'changes' => [
                ['target_type' => 'dish-variants', 'target_id' => $this->size->id, 'fields' => ['price' => 150]],
                ['target_type' => 'dishes', 'target_id' => $this->dish->id, 'fields' => [
                    'title' => ['en' => 'Chicken soup', 'uk' => 'Курячий суп'],
                ]],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Winter menu')
            ->assertJsonPath('data.status', MenuVersion::STATUS_SCHEDULED)
            // Kyiv is UTC+2 in winter
            ->assertJsonPath('data.goes_live_at', '2030-11-01T00:00:00+02:00')
            ->assertJsonPath('data.timezone', 'Europe/Kyiv')
            ->assertJsonPath('data.created_by.name', $this->admin->name)
            ->assertJsonPath('data.changes_count', 2)
            ->assertJsonPath('data.items_count', 2)
            ->assertJsonPath('data.changes.0.fields.price', ['live' => 140, 'new' => 150])
            ->assertJsonPath('data.changes.0.label', [
                'name' => 'Chicken broth',
                'size' => ['weight' => '350', 'weight_unit' => 'g'],
                'path' => ['Main menu', 'Soups'],
            ])
            ->assertJsonPath('data.changes.1.fields.title.new', ['en' => 'Chicken soup', 'uk' => 'Курячий суп'])
            ->assertJsonPath('data.changes.1.conflicts', []);

        /** @var MenuVersion $version */
        $version = MenuVersion::query()->sole();
        $this->assertSame('2030-10-31 22:00:00', $version->goes_live_at->toDateTimeString());
        $this->assertEquals(140, $this->size->fresh()->price);
        $this->assertSame('Chicken broth', $this->dish->fresh()->title);
    }

    /**
     * Test that fields of an item are merged into its change, a field back at its live value
     * isn't a change anymore, and a change without fields is gone.
     *
     * @return void
     */
    public function testChangesAreMergedAndReverted()
    {
        $version = $this->draft();
        $item = ['target_type' => 'dish-variants', 'target_id' => $this->size->id];

        $this->putChange($version, [...$item, 'fields' => ['price' => 150]])
            ->assertOk()
            ->assertJsonPath('data.changes_count', 1);

        $this->putChange($version, [...$item, 'fields' => ['calories' => 210]])
            ->assertOk()
            ->assertJsonPath('data.items_count', 1)
            ->assertJsonPath('data.changes_count', 2);

        // the price is back at its live value
        $this->putChange($version, [...$item, 'fields' => ['price' => '140.00']])
            ->assertOk()
            ->assertJsonPath('data.changes.0.fields.calories', ['live' => null, 'new' => 210])
            ->assertJsonPath('data.changes_count', 1);

        $this->putChange($version, [...$item, 'revert' => ['calories']])
            ->assertOk()
            ->assertJsonPath('data.items_count', 0)
            ->assertJsonPath('data.changes', []);

        $this->assertSame(0, MenuVersionChange::query()->count());
    }

    /**
     * Test that the live value from when a field was planned is kept, and a live value, which
     * changed since, is a conflict.
     *
     * @return void
     */
    public function testChangedLiveValuesAreConflicts()
    {
        $version = $this->draft();
        $item = ['target_type' => 'dish-variants', 'target_id' => $this->size->id];

        $this->putChange($version, [...$item, 'fields' => ['price' => 150]])->assertOk();

        $this->size->update(['price' => 145]);

        $this->putChange($version, [...$item, 'fields' => ['calories' => 210]])
            ->assertOk()
            ->assertJsonPath('data.changes.0.fields.price', ['live' => 140, 'new' => 150]);

        $this->getJson("/api/editor/versions/{$version->id}")
            ->assertOk()
            ->assertJsonPath('data.changes.0.conflicts', ['fields' => ['price' => 145]]);

        $this->dish->delete();

        $this->getJson("/api/editor/versions/{$version->id}")
            ->assertOk()
            ->assertJsonPath('data.changes.0.conflicts', ['missing' => true]);
    }

    /**
     * Test that the values are checked like the editor checks them, and items have to be
     * the restaurant's.
     *
     * @return void
     */
    public function testChangesAreValidated()
    {
        $version = $this->draft();
        $foreign = Dish::factory()
            ->withMenu(DishMenu::factory()->withRestaurant(Restaurant::factory()->create())->create())
            ->create();

        $this->putChange($version, [
            'target_type' => 'dishes',
            'target_id' => $this->dish->id,
            'fields' => [
                'title' => ['en' => ''],
                'badge' => ['en' => str_repeat('a', 26)],
                'flags' => ['spicy-as-hell'],
                'price' => 10,
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.title.en', 'fields.badge.en', 'fields.flags.0', 'fields']);

        $this->putChange($version, [
            'target_type' => 'dishes',
            'target_id' => $foreign->id,
            'fields' => ['is_hidden' => true],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['target_id']);

        $this->putChange($version, ['target_type' => 'menus', 'target_id' => 1])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['target_type']);

        // menus can't be added in a version
        $this->putChange($version, ['target_type' => 'dish-menus', 'fields' => ['title' => ['en' => 'Bar']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['target_id']);

        // brand colors have to be readable
        $this->putChange($version, [
            'target_type' => 'restaurants',
            'target_id' => $this->restaurant->id,
            'fields' => ['brand_primary' => '#ffffff', 'brand_primary_content' => '#fefefe'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.brand_primary_content']);

        $this->assertSame(0, MenuVersionChange::query()->count());
    }

    /**
     * Test that new items can be added: a dish with its sizes, a size of a dish, a note.
     *
     * @return void
     */
    public function testNewItemsCanBeAdded()
    {
        $version = $this->draft();
        $photo = MediaFactory::new()->create(['restaurant_id' => $this->restaurant->id]);

        $this->putChange($version, [
            'target_type' => 'dishes',
            'parent_id' => $this->category->id,
            'fields' => ['title' => ['en' => 'Pumpkin cream soup']],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields.sizes']);

        $id = $this->putChange($version, [
            'target_type' => 'dishes',
            'parent_id' => $this->category->id,
            'fields' => [
                'title' => ['en' => 'Pumpkin cream soup'],
                'flags' => ['vegetarian', 'alg-milk'],
                'sizes' => [['price' => 170, 'weight' => 300, 'weight_unit' => 'g', 'preparation_time' => 12]],
                'media' => [['id' => $photo->id]],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.changes.0.is_new', true)
            ->assertJsonPath('data.changes.0.changes_count', 1)
            ->assertJsonPath('data.changes.0.label', [
                'name' => 'Pumpkin cream soup',
                'size' => null,
                'path' => ['Main menu', 'Soups'],
            ])
            ->json('data.changes.0.id');

        // the new dish's change is edited by its id
        $this->putChange($version, ['id' => $id, 'target_type' => 'dishes', 'fields' => ['badge' => ['en' => 'New']]])
            ->assertOk()
            ->assertJsonPath('data.items_count', 1)
            ->assertJsonPath('data.changes.0.fields.badge.new.en', 'New');

        $this->putChange($version, [
            'target_type' => 'dish-variants',
            'parent_id' => $this->dish->id,
            'fields' => ['price' => 190, 'weight' => 500, 'weight_unit' => 'g'],
        ])->assertOk();

        $this->putChange($version, [
            'target_type' => 'restaurant-notes',
            'parent_id' => $this->restaurant->id,
            'fields' => ['text' => ['en' => 'Live music every Friday from 19:00.']],
        ])->assertOk()->assertJsonPath('data.items_count', 3);

        $this->assertSame(1, Dish::query()->withoutGlobalScopes()->count());

        $this->postJson("/api/editor/versions/{$version->id}/apply")
            ->assertOk()
            ->assertJsonPath('data.status', MenuVersion::STATUS_APPLIED);

        /** @var Dish $soup */
        $soup = Dish::query()->withoutGlobalScopes()->where('title->en', 'Pumpkin cream soup')->sole();
        $this->assertSame($this->category->id, $soup->category_id);
        $this->assertSame('New', $soup->badge);
        $this->assertEquals(170, $soup->price);
        $this->assertSame(['alg-milk', 'vegetarian'], $soup->flags);
        $this->assertSame([$photo->id], $soup->media()->pluck('media.id')->all());
        $this->assertEquals([140, 190], $this->dish->variants()->pluck('price')->all());
        /** @var RestaurantNote $note */
        $note = $this->restaurant->notes()->sole();
        $this->assertSame('Live music every Friday from 19:00.', $note->text);
    }

    /**
     * Test that a version can't leave a dish without a size guests see.
     *
     * @return void
     */
    public function testDishKeepsASizeGuestsSee()
    {
        $version = $this->draft();
        $hide = ['target_type' => 'dish-variants', 'target_id' => $this->size->id, 'fields' => ['is_hidden' => true]];

        $this->putChange($version, $hide)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields' => DishVariant::LAST_SIZE_MESSAGE]);

        $this->putChange($version, [...$hide, 'fields' => ['archived' => true]])
            ->assertUnprocessable();

        // with a new size, the old one can be hidden
        $large = $this->putChange($version, [
            'target_type' => 'dish-variants',
            'parent_id' => $this->dish->id,
            'fields' => ['price' => 190],
        ])->assertOk()->json('data.changes.0.id');

        $this->putChange($version, $hide)->assertOk();

        // but the new one can't go then
        $this->deleteJson("/api/editor/versions/{$version->id}/changes/{$large}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['fields' => DishVariant::LAST_SIZE_MESSAGE]);

        $this->postJson("/api/editor/versions/{$version->id}/apply")->assertOk();

        $this->assertTrue($this->size->fresh()->is_hidden);
        $this->assertEquals(190, $this->dish->fresh()->price);
    }

    /**
     * Test the statuses: a draft is scheduled at its date in the future, a scheduled version
     * can be deactivated (it keeps its date) and activated again.
     *
     * @return void
     */
    public function testVersionIsScheduledDeactivatedAndActivated()
    {
        $version = $this->draft();
        $url = "/api/editor/versions/{$version->id}";

        $this->postJson("$url/schedule")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['goes_live_at']);

        $this->postJson("$url/deactivate")->assertUnprocessable()->assertJsonValidationErrors(['status']);

        $this->patchJson($url, ['name' => 'Winter menu', 'goes_live_at' => '2030-11-01 00:00'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Winter menu')
            ->assertJsonPath('data.status', MenuVersion::STATUS_DRAFT);

        $this->postJson("$url/schedule")->assertOk()->assertJsonPath('data.status', MenuVersion::STATUS_SCHEDULED);

        // a scheduled version stays in the future
        $this->patchJson($url, ['goes_live_at' => '2020-01-01 00:00'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['goes_live_at']);

        $this->postJson("$url/deactivate")->assertOk()->assertJsonPath('data.status', MenuVersion::STATUS_INACTIVE);
        $this->postJson("$url/activate")->assertOk()->assertJsonPath('data.status', MenuVersion::STATUS_SCHEDULED);

        $this->assertSame('2030-10-31 22:00:00', $version->fresh()->goes_live_at->toDateTimeString());
    }

    /**
     * Test that applying a version applies all its changes: texts only in the languages it
     * changes, photos, hiding and archiving.
     *
     * @return void
     */
    public function testApplyingAppliesAllTheChanges()
    {
        $this->dish->update(['title' => ['en' => 'Chicken broth', 'uk' => 'Курячий бульйон']]);
        /** @var RestaurantNote $note */
        $note = RestaurantNote::factory()->withRestaurant($this->restaurant)->create(['text' => 'Card payments only.']);
        $bar = DishMenu::factory()->withRestaurant($this->restaurant)->create(['title' => 'Summer terrace']);
        [$first, $second] = MediaFactory::new()->count(2)->create(['restaurant_id' => $this->restaurant->id]);

        $version = $this->draft();
        $this->putChange($version, [
            'target_type' => 'dishes',
            'target_id' => $this->dish->id,
            'fields' => [
                'title' => ['en' => 'Chicken soup', 'uk' => 'Курячий бульйон'],
                'media' => [['id' => $second->id], ['id' => $first->id, 'is_hidden' => true]],
            ],
        ])->assertOk();
        $this->putChange($version, ['target_type' => 'dish-variants', 'target_id' => $this->size->id, 'fields' => [
            'price' => 150,
        ]])->assertOk();
        $this->putChange($version, ['target_type' => 'restaurant-notes', 'target_id' => $note->id, 'fields' => [
            'is_hidden' => true,
        ]])->assertOk();
        $this->putChange($version, ['target_type' => 'dish-menus', 'target_id' => $bar->id, 'fields' => [
            'archived' => true,
        ]])->assertOk();

        // the Ukrainian title is changed in the meantime, the version doesn't change it
        $this->dish->update(['title' => ['en' => 'Chicken broth', 'uk' => 'Бульйон']]);

        Sanctum::actingAs($this->admin, ['*']);

        $this->postJson("/api/editor/versions/{$version->id}/apply")
            ->assertOk()
            ->assertJsonPath('data.status', MenuVersion::STATUS_APPLIED);

        $dish = $this->dish->fresh();
        $this->assertSame(['en' => 'Chicken soup', 'uk' => 'Бульйон'], $dish->getTranslations('title'));
        $this->assertSame([$second->id], $dish->media()->pluck('media.id')->all());
        $this->assertSame([$second->id, $first->id], $dish->allMedia()->pluck('media.id')->all());
        $this->assertEquals(150, $this->size->fresh()->price);
        $this->assertEquals(150, $dish->price);
        $this->assertTrue($note->fresh()->is_hidden);

        /** @var DishMenu $bar */
        $bar = DishMenu::query()->withoutGlobalScopes()->findOrFail($bar->id);
        $this->assertTrue((bool) $bar->archived);
        $this->assertNotNull($bar->archived_at);

        $this->assertNotNull($version->fresh()->applied_at);
        $this->assertSame($this->admin->id, $this->restaurant->fresh()->last_saved_by);

        // a version, which went live, can't be changed
        $this->putChange($version, ['target_type' => 'dish-variants', 'target_id' => $this->size->id, 'fields' => [
            'price' => 160,
        ]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test that a version, which can't be applied, fails with the reason, and nothing of it is applied.
     *
     * @return void
     */
    public function testFailedVersionAppliesNothing()
    {
        $soup = Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['title' => 'Soup']);

        $version = $this->draft();
        $this->putChange($version, ['target_type' => 'dish-variants', 'target_id' => $this->size->id, 'fields' => [
            'price' => 150,
        ]])->assertOk();
        $this->putChange($version, ['target_type' => 'dishes', 'target_id' => $soup->id, 'fields' => [
            'is_hidden' => true,
        ]])->assertOk();

        $soup->delete();

        $this->postJson("/api/editor/versions/{$version->id}/apply")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Failed')
            ->assertJsonPath('data.status', MenuVersion::STATUS_FAILED)
            ->assertJsonPath('data.failure_reason', 'A changed dish doesn\'t exist anymore.');

        $this->assertEquals(140, $this->size->fresh()->price);
        $this->assertNotNull($version->fresh()->failed_at);
    }

    /**
     * Test that a version is copied as a draft with the same changes, and deleted with them.
     *
     * @return void
     */
    public function testVersionIsDuplicatedAndDeleted()
    {
        $version = $this->draft(['name' => 'Winter menu']);
        $this->putChange($version, ['target_type' => 'dish-variants', 'target_id' => $this->size->id, 'fields' => [
            'price' => 150,
        ]])->assertOk();
        $this->postJson("/api/editor/versions/{$version->id}/schedule")->assertUnprocessable();

        $copy = $this->postJson("/api/editor/versions/{$version->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.name', 'Winter menu (copy)')
            ->assertJsonPath('data.status', MenuVersion::STATUS_DRAFT)
            ->assertJsonPath('data.changes.0.fields.price', ['live' => 140, 'new' => 150])
            ->json('data.id');

        $this->deleteJson("/api/editor/versions/{$version->id}")->assertOk();

        $this->assertModelMissing($version);
        $this->assertSame(1, MenuVersionChange::query()->count());
        /** @var MenuVersionChange $change */
        $change = MenuVersionChange::query()->sole();
        $this->assertSame($copy, $change->version_id);
    }

    /**
     * Test that versions are listed pending ones first by their date (undated drafts after
     * them), then the ones, which went live; the editor's restaurant has the pending ones.
     *
     * @return void
     */
    public function testVersionsAreListedInTheirOrder()
    {
        $later = $this->draft(['status' => MenuVersion::STATUS_SCHEDULED, 'goes_live_at' => now()->addMonth()]);
        $sooner = $this->draft(['status' => MenuVersion::STATUS_INACTIVE, 'goes_live_at' => now()->addWeek()]);
        $undated = $this->draft();
        $applied = $this->draft(['status' => MenuVersion::STATUS_APPLIED, 'goes_live_at' => now()->subWeek()]);
        MenuVersion::factory()->withRestaurant(Restaurant::factory()->create())->create();

        $url = "/api/editor/restaurants/{$this->restaurant->id}/versions";

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.*.id', [$sooner->id, $later->id, $undated->id, $applied->id]);

        $this->getJson("$url?pending=1")
            ->assertOk()
            ->assertJsonPath('data.*.id', [$sooner->id, $later->id, $undated->id]);

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}")
            ->assertOk()
            ->assertJsonPath('data.versions.*.id', [$sooner->id, $later->id, $undated->id]);
    }

    /**
     * Test that versions are managed only by admins of their restaurant.
     *
     * @return void
     */
    public function testOnlyTheRestaurantsAdminsManageVersions()
    {
        $version = $this->draft();
        $other = Restaurant::factory()->create();

        Sanctum::actingAs($this->user(UserRole::Admin, $other), ['*']);

        $this->getJson("/api/editor/versions/{$version->id}")->assertForbidden();
        $this->postJson("/api/editor/versions/{$version->id}/apply")->assertForbidden();
        $this->postJson("/api/editor/restaurants/{$this->restaurant->id}/versions", [])->assertForbidden();

        Sanctum::actingAs($this->user(UserRole::Manager, $this->restaurant), ['*']);

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}/versions")->assertForbidden();
        $this->putChange($version, ['target_type' => 'dishes', 'target_id' => $this->dish->id, 'fields' => [
            'is_hidden' => true,
        ]])->assertForbidden();
    }

    /**
     * Test that versions have their restaurant's dates, also without a time zone.
     *
     * @return void
     */
    public function testDatesAreInTheRestaurantsTimeZone()
    {
        $this->restaurant->update(['timezone' => '']);

        $version = $this->draft(['goes_live_at' => Carbon::parse('2030-11-01 10:00', 'UTC')]);

        $this->getJson("/api/editor/versions/{$version->id}")
            ->assertOk()
            ->assertJsonPath('data.goes_live_at', '2030-11-01T10:00:00+00:00');
    }
}
