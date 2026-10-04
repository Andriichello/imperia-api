<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Helpers\WebCacheHelper;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Morphs\Media;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\Schedule;
use App\Models\ScheduleException;
use Database\Factories\Morphs\MediaFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

/**
 * Class RestaurantEditorTest.
 *
 * The editor's restaurant: its payload, details, notes, photos and hours.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RestaurantEditorTest extends EditorTestCase
{
    /**
     * Test that only the restaurant's admins and admins without a restaurant can edit it.
     *
     * @return void
     */
    public function testOnlyAdminsOfTheRestaurantCanEditIt()
    {
        $url = "/api/editor/restaurants/{$this->restaurant->id}";

        $this->getJson($url)->assertOk();

        Sanctum::actingAs($this->user(UserRole::Admin), ['*']);
        $this->getJson($url)->assertOk();

        Sanctum::actingAs($this->user(UserRole::Admin, Restaurant::factory()->create()), ['*']);
        $this->getJson($url)->assertForbidden();
        $this->patchJson($url, ['phone' => '+380441234567'])->assertForbidden();

        Sanctum::actingAs($this->user(UserRole::Manager, $this->restaurant), ['*']);
        $this->getJson($url)->assertForbidden();
        $this->getJson('/api/editor/restaurants')->assertForbidden();

        Sanctum::actingAs($this->user(UserRole::Customer), ['*']);
        $this->getJson($url)->assertForbidden();

        $this->getJson('/api/editor/restaurants/0')->assertNotFound();
    }

    /**
     * Test that guests can't use the editor.
     *
     * @return void
     */
    public function testGuestsCannotUseTheEditor()
    {
        $this->app['auth']->forgetGuards();

        $this->getJson("/api/editor/restaurants/{$this->restaurant->id}")->assertUnauthorized();
        $this->getJson('/api/editor/restaurants')->assertUnauthorized();
    }

    /**
     * Test that the list has only the restaurants the user can edit.
     *
     * @return void
     */
    public function testRestaurantsAreListedForTheirAdmins()
    {
        $other = Restaurant::factory()->create();

        $this->getJson('/api/editor/restaurants')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$this->restaurant->id]);

        Sanctum::actingAs($this->user(UserRole::Admin), ['*']);

        $this->getJson('/api/editor/restaurants')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$this->restaurant->id, $other->id]);
    }

    /**
     * Test that the payload has everything in all languages, hidden and archived items included,
     * but not deleted ones.
     *
     * @return void
     */
    public function testPayloadHasEverythingInAllLanguages()
    {
        $main = DishMenu::factory()->withRestaurant($this->restaurant)
            ->create(['title' => ['en' => 'Main menu', 'uk' => 'Основне меню'], 'popularity' => 2]);
        $old = DishMenu::factory()->withRestaurant($this->restaurant)
            ->create(['title' => 'Winter specials', 'archived' => true, 'popularity' => 1]);
        DishMenu::factory()->withRestaurant($this->restaurant)->create()->delete();

        $soups = DishCategory::factory()->withMenu($main)->create(['title' => 'Soups', 'popularity' => 2]);
        $salads = DishCategory::factory()->withMenu($main)
            ->create(['title' => 'Salads', 'is_hidden' => true, 'popularity' => 1]);

        $borscht = Dish::factory()->withMenu($main)->withCategory($soups)->create([
            'title' => ['en' => 'Borscht', 'uk' => 'Борщ'],
            'popularity' => 2,
            'badge' => 'New',
            'price' => 185,
            'weight' => '300',
            'weight_unit' => 'g',
            'flags' => ['alg-milk'],
        ]);
        DishVariant::factory()->withDish($borscht)->create(['price' => 245, 'weight' => '450', 'weight_unit' => 'g']);
        Dish::factory()->withMenu($main)->withCategory($soups)
            ->create(['title' => 'Fish soup', 'archived' => true, 'popularity' => 1]);

        RestaurantNote::factory()->withRestaurant($this->restaurant)->create(['text' => 'Card payments only.']);
        Schedule::factory()->withRestaurant($this->restaurant)->withWeekday('monday')
            ->create(['beg_hour' => 10, 'beg_minute' => 0, 'end_hour' => 22, 'end_minute' => 0]);

        $response = $this->getJson("/api/editor/restaurants/{$this->restaurant->id}")
            ->assertOk()
            ->assertJsonPath('data.name', ['en' => 'Smak', 'uk' => null])
            ->assertJsonPath('data.default_locale', 'en')
            ->assertJsonPath('data.supported_locales', ['en', 'uk'])
            ->assertJsonPath('data.notes.0.text.en', 'Card payments only.')
            ->assertJsonPath('data.weekdays.monday.0.end_hour', 22)
            ->assertJsonPath('data.weekdays.sunday', [])
            ->assertJsonPath('data.menus.*.id', [$main->id, $old->id])
            ->assertJsonPath('data.menus.0.title', ['en' => 'Main menu', 'uk' => 'Основне меню'])
            ->assertJsonPath('data.menus.1.archived', true)
            ->assertJsonPath('data.menus.0.categories.*.id', [$soups->id, $salads->id])
            ->assertJsonPath('data.menus.0.categories.1.is_hidden', true)
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.title.uk', 'Борщ')
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.badge', ['en' => 'New', 'uk' => null])
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.flags', ['alg-milk'])
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.sizes.*.price', [185, 245])
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.sizes.0.id', $borscht->sizes()->value('id'))
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.sizes.0.is_hidden', false)
            ->assertJsonPath('data.menus.0.categories.0.dishes.0.archived_sizes', [])
            ->assertJsonPath('data.menus.0.categories.0.dishes.1.archived', true);

        $this->assertCount(2, $response->json('data.menus'));
    }

    /**
     * Test that details are updated, and that what's left out stays as it is.
     *
     * @return void
     */
    public function testDetailsAreUpdated()
    {
        $this->restaurant->update(['phone' => '+380441111111']);

        $this->patchJson("/api/editor/restaurants/{$this->restaurant->id}", [
            'name' => ['en' => 'Smak', 'uk' => 'Смак'],
            'establishment' => 'cafe',
            'address' => ['en' => '12 Khreshchatyk St, Kyiv', 'uk' => ''],
        ])
            ->assertOk()
            ->assertJsonPath('data.name', ['en' => 'Smak', 'uk' => 'Смак'])
            ->assertJsonPath('data.address', ['en' => '12 Khreshchatyk St, Kyiv', 'uk' => null])
            ->assertJsonPath('data.establishment', 'cafe')
            ->assertJsonPath('data.phone', '+380441111111');

        $restaurant = $this->restaurant->fresh();

        $this->assertSame('Смак', $restaurant->getTranslation('name', 'uk'));
        // the public page shows the address, in the current language
        $this->assertSame('12 Khreshchatyk St, Kyiv', $restaurant->full_address);
    }

    /**
     * Test that the name is required in the default language and other values are checked.
     *
     * @return void
     */
    public function testDetailsAreValidated()
    {
        $this->patchJson("/api/editor/restaurants/{$this->restaurant->id}", [
            'name' => ['en' => '', 'uk' => 'Смак'],
            'establishment' => 'castle',
            'phone' => str_repeat('1', 31),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name.en', 'establishment', 'phone']);

        $this->patchJson("/api/editor/restaurants/{$this->restaurant->id}", [
            'name' => ['en' => 'Smak', 'pl' => 'Smak'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test that brand colors are saved only when text on their tints is readable.
     *
     * @return void
     */
    public function testBrandColorsNeedReadableText()
    {
        $url = "/api/editor/restaurants/{$this->restaurant->id}";

        $this->patchJson($url, ['brand_primary' => '#6DB0BB', 'brand_primary_content' => '#295a5a'])
            ->assertOk()
            ->assertJsonPath('data.brand_primary', '#6db0bb')
            ->assertJsonPath('data.brand_primary_content', '#295a5a');

        $this->patchJson($url, ['brand_primary' => '#3bb517', 'brand_primary_content' => '#a0d090'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand_primary_content']);

        $this->patchJson($url, ['brand_primary' => '#3bb517'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand_primary_content']);

        $this->patchJson($url, ['brand_primary' => 'green', 'brand_primary_content' => '#284625'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand_primary']);

        $this->assertSame('#6db0bb', $this->restaurant->fresh()->brand_primary);
    }

    /**
     * Test that the accent color is saved only when prices in it are readable on the menu,
     * and that it can be cleared (the page mixes the brand colors then).
     *
     * @return void
     */
    public function testAccentColorNeedsReadablePrices()
    {
        $url = "/api/editor/restaurants/{$this->restaurant->id}";

        $this->patchJson($url, ['brand_accent' => '#4B858B'])
            ->assertOk()
            ->assertJsonPath('data.brand_accent', '#4b858b');

        // light green prices on the light gray menu are hard to read
        $this->patchJson($url, ['brand_accent' => '#71d855'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand_accent']);

        $this->patchJson($url, ['brand_accent' => 'teal'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['brand_accent']);

        $this->assertSame('#4b858b', $this->restaurant->fresh()->brand_accent);

        $this->patchJson($url, ['brand_accent' => null])
            ->assertOk()
            ->assertJsonPath('data.brand_accent', null);
    }

    /**
     * Test that notes are replaced in their order, and the website shows only visible ones.
     *
     * @return void
     */
    public function testNotesAreReplacedInTheirOrder()
    {
        /** @var RestaurantNote $kept */
        $kept = RestaurantNote::factory()->withRestaurant($this->restaurant)->create(['text' => 'Card payments only.']);
        /** @var RestaurantNote $removed */
        $removed = RestaurantNote::factory()->withRestaurant($this->restaurant)->create(['text' => 'Old note']);

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/notes", [
            'notes' => [
                ['text' => ['en' => 'Live music on Fridays.', 'uk' => 'Жива музика щоп\'ятниці.']],
                ['id' => $kept->id, 'text' => ['en' => 'Cards only.', 'uk' => null]],
                ['text' => ['en' => 'Closed on 14 October.'], 'is_hidden' => true],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('data.notes.*.text.en', ['Live music on Fridays.', 'Cards only.', 'Closed on 14 October.'])
            ->assertJsonPath('data.notes.*.is_hidden', [false, false, true])
            ->assertJsonPath('data.notes.1.id', $kept->id);

        $this->assertModelMissing($removed);
        $this->assertSame([1, 2, 3], $this->restaurant->notes()->pluck('order')->all());

        $this->get("/uk/web/{$this->restaurant->id}")
            ->assertOk()
            ->assertViewHas('restaurant', function ($resource) {
                return $resource->resolve()['notes'] === ['Жива музика щоп\'ятниці.', 'Cards only.'];
            });
    }

    /**
     * Test that notes need text in the default language, and only the restaurant's notes can be kept.
     * Messages name the fields.
     *
     * @return void
     */
    public function testNotesAreValidated()
    {
        /** @var RestaurantNote $other */
        $other = RestaurantNote::factory()->withRestaurant(Restaurant::factory()->create())->create();

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/notes", [
            'notes' => [
                ['text' => ['uk' => 'Лише українською']],
                ['id' => $other->id, 'text' => ['en' => 'Taken']],
                ['text' => ['en' => str_repeat('a', 121)]],
            ],
        ])
            ->assertUnprocessable()
            // fields are named in messages
            ->assertJsonValidationErrors([
                'notes.0.text.en' => 'The note field is required.',
                'notes.1.id' => 'The selected note is invalid.',
                'notes.2.text.en' => 'The note must not be greater than 120 characters.',
            ]);

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/notes", ['notes' => []])
            ->assertOk()
            ->assertJsonPath('data.notes', []);
    }

    /**
     * Test that photos are set in their order, hidden ones are kept (guests don't see them),
     * and only the restaurant's ones can be set.
     *
     * @return void
     */
    public function testPhotosAreSetInTheirOrder()
    {
        [$first, $second] = MediaFactory::new()->count(2)->create(['restaurant_id' => $this->restaurant->id]);
        $foreign = MediaFactory::new()->create(['restaurant_id' => Restaurant::factory()->create()->id]);

        Cache::put(WebCacheHelper::restaurantKey($this->restaurant->id), 'cached');

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/photos", [
            'media' => [['id' => $second->id], ['id' => $first->id, 'is_hidden' => true]],
        ])
            ->assertOk()
            ->assertJsonPath('data.photos.*.id', [$second->id, $first->id])
            ->assertJsonPath('data.photos.*.is_hidden', [false, true]);

        // attaching photos fires no events, the website is forgotten anyway
        $this->assertFalse(Cache::has(WebCacheHelper::restaurantKey($this->restaurant->id)));
        $this->assertSame([$second->id], $this->restaurant->media()->pluck('media.id')->all());

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/photos", ['media' => [['id' => $foreign->id]]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['media.0.id']);

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/photos", ['media' => []])
            ->assertOk()
            ->assertJsonPath('data.photos', []);
    }

    /**
     * Test that a photo is uploaded for the restaurant: it's unattached till it's set as a photo.
     *
     * @return void
     */
    public function testPhotosAreUploadedForTheRestaurant()
    {
        Storage::fake(config('media.disk'));
        $other = Restaurant::factory()->create();
        $url = "/api/editor/restaurants/{$this->restaurant->id}/media";

        $id = $this->postJson($url, ['file' => UploadedFile::fake()->image('dining-room.jpg', 800, 600)])
            ->assertCreated()
            ->assertJsonPath('data.title', 'dining-room.jpg')
            ->json('data.id');

        /** @var Media $media */
        $media = Media::query()->findOrFail($id);

        $this->assertSame($this->restaurant->id, $media->restaurant_id);
        $this->assertStringEndsWith("/{$this->restaurant->id}/", $media->folder);
        $this->assertSame(0, $media->mediables()->count());

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/photos", ['media' => [['id' => $id]]])
            ->assertOk()
            ->assertJsonPath('data.photos.0.id', $id);

        $this->postJson($url, ['file' => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        $this->postJson($url, ['file' => UploadedFile::fake()->image('huge.jpg')->size(11 * 1024)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);

        $this->postJson("/api/editor/restaurants/{$other->id}/media", ['file' => UploadedFile::fake()->image('a.jpg')])
            ->assertForbidden();
    }

    /**
     * Test that hours are replaced: several intervals a day, closed days, special days and a closure.
     *
     * @return void
     */
    public function testHoursAreReplaced()
    {
        Schedule::factory()->withRestaurant($this->restaurant)->withWeekday('sunday')->create();
        /** @var ScheduleException $removed */
        $removed = ScheduleException::factory()->withRestaurant($this->restaurant)->create();
        /** @var ScheduleException $kept */
        $kept = ScheduleException::factory()->withRestaurant($this->restaurant)->create();

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/hours", [
            'timezone' => 'Europe/Warsaw',
            'weekdays' => [
                'monday' => [
                    ['beg_hour' => 16, 'beg_minute' => 0, 'end_hour' => 22, 'end_minute' => 0],
                    ['beg_hour' => 10, 'beg_minute' => 0, 'end_hour' => 14, 'end_minute' => 30],
                ],
                'friday' => [
                    ['beg_hour' => 18, 'beg_minute' => 0, 'end_hour' => 2, 'end_minute' => 0],
                ],
            ],
            'exceptions' => [
                ['id' => $kept->id, 'starts_on' => '2026-12-24', 'is_closed' => false,
                    'beg_hour' => 10, 'beg_minute' => 0, 'end_hour' => 18, 'end_minute' => 0,
                    'reason' => ['en' => 'Christmas Eve', 'uk' => 'Святвечір']],
                ['starts_on' => '2027-01-01', 'ends_on' => '2027-01-02', 'is_closed' => true,
                    'reason' => ['en' => 'New Year']],
            ],
            'closed_until' => '2026-10-15',
            'closed_reason' => ['en' => 'Renovation'],
        ])
            ->assertOk()
            ->assertJsonPath('data.timezone', 'Europe/Warsaw')
            ->assertJsonPath('data.weekdays.monday.*.beg_hour', [10, 16])
            ->assertJsonPath('data.weekdays.friday.0.end_hour', 2)
            ->assertJsonPath('data.weekdays.sunday', [])
            ->assertJsonPath('data.exceptions.*.starts_on', ['2026-12-24', '2027-01-01'])
            ->assertJsonPath('data.exceptions.0.id', $kept->id)
            ->assertJsonPath('data.exceptions.0.reason', ['en' => 'Christmas Eve', 'uk' => 'Святвечір'])
            ->assertJsonPath('data.exceptions.1.ends_on', '2027-01-02')
            ->assertJsonPath('data.exceptions.1.beg_hour', null)
            ->assertJsonPath('data.closed_until', '2026-10-15')
            ->assertJsonPath('data.closed_reason.en', 'Renovation');

        $this->assertModelMissing($removed);
        $this->assertSame(3, $this->restaurant->schedules()->count());
    }

    /**
     * Test that intervals of a day can't overlap or be empty, and open special days need hours.
     *
     * @return void
     */
    public function testHoursAreValidated()
    {
        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/hours", [
            'timezone' => 'Europe/Kyiv',
            'weekdays' => [
                'monday' => [
                    ['beg_hour' => 10, 'beg_minute' => 0, 'end_hour' => 14, 'end_minute' => 0],
                    ['beg_hour' => 13, 'beg_minute' => 0, 'end_hour' => 22, 'end_minute' => 0],
                ],
                'tuesday' => [
                    ['beg_hour' => 10, 'beg_minute' => 0, 'end_hour' => 10, 'end_minute' => 0],
                ],
                'friday' => [
                    // after midnight, then the same night again
                    ['beg_hour' => 20, 'beg_minute' => 0, 'end_hour' => 2, 'end_minute' => 0],
                    ['beg_hour' => 23, 'beg_minute' => 0, 'end_hour' => 23, 'end_minute' => 30],
                ],
            ],
            'exceptions' => [
                ['starts_on' => '2026-12-24', 'is_closed' => false],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'weekdays.monday.1.beg_hour',
                'weekdays.tuesday.0.end_hour',
                'weekdays.friday.1.beg_hour',
                'exceptions.0.beg_hour',
            ]);

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/hours", [
            'timezone' => 'Mars/Olympus',
            'weekdays' => ['someday' => []],
            'exceptions' => [
                ['starts_on' => '2026-12-24', 'ends_on' => '2026-12-20', 'is_closed' => true],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['timezone', 'weekdays', 'exceptions.0.ends_on']);
    }

    /**
     * Test that saving notes forgets the cached website page.
     *
     * @return void
     */
    public function testWebsiteIsForgottenOnChanges()
    {
        $key = WebCacheHelper::restaurantKey($this->restaurant->id);
        Cache::put($key, 'cached');

        $this->putJson("/api/editor/restaurants/{$this->restaurant->id}/notes", [
            'notes' => [['text' => ['en' => 'Pets welcome']]],
        ])->assertOk();

        $this->assertFalse(Cache::has($key));
    }
}
