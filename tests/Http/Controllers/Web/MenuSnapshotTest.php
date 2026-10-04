<?php

namespace Tests\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Helpers\WebCacheHelper;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\MenuSnapshot;
use App\Models\Morphs\Media;
use App\Models\Restaurant;
use App\Models\User;
use Database\Factories\Morphs\MediaFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Class MenuSnapshotTest.
 *
 * All dishes guests see on a restaurant's pages, from a gzipped JSON file named after its content,
 * which is built once after a change.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MenuSnapshotTest extends TestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

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
     * Where the files are kept.
     *
     * @var FilesystemAdapter
     */
    protected FilesystemAdapter $disk;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['menu_snapshots.disk' => 'snapshots', 'menu_snapshots.direct' => false]);
        $this->disk = Storage::fake('snapshots');

        $this->restaurant = Restaurant::factory()->create();
        $this->menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $this->category = DishCategory::factory()->withMenu($this->menu)->create();
        $this->dish = Dish::factory()->withMenu($this->menu)->withCategory($this->category)
            ->create(['title' => 'Borscht']);
    }

    /**
     * The restaurant's dishes in the language.
     *
     * @param string $locale
     * @param array $query
     * @param array $headers
     *
     * @return TestResponse
     */
    protected function dishes(string $locale = 'en', array $query = [], array $headers = []): TestResponse
    {
        return $this->get(
            "/api/restaurants/{$this->restaurant->id}/dishes?" . http_build_query(['locale' => $locale, ...$query]),
            ['Accept-Encoding' => 'gzip, br', ...$headers],
        );
    }

    /**
     * The latest snapshot.
     *
     * @return MenuSnapshot
     */
    protected function latestSnapshot(): MenuSnapshot
    {
        /** @var MenuSnapshot $snapshot */
        $snapshot = MenuSnapshot::query()->latest('id')->firstOrFail();

        return $snapshot;
    }

    /**
     * The dishes of the answer.
     *
     * @param TestResponse $response
     *
     * @return array
     */
    protected function dataOf(TestResponse $response): array
    {
        $body = $response->getContent();

        $json = $response->headers->get('Content-Encoding') === 'gzip' ? gzdecode($body) : $body;

        return json_decode($json, true)['data'];
    }

    /**
     * Test that the dishes are built once into a gzipped file, which the next requests get.
     *
     * @return void
     */
    public function testDishesAreBuiltOnceAndKept()
    {
        $first = $this->dishes()->assertOk()->assertHeader('Content-Encoding', 'gzip');

        $this->assertSame(['Borscht'], array_column($this->dataOf($first), 'title'));

        /** @var MenuSnapshot $snapshot */
        $snapshot = $this->latestSnapshot();

        $this->assertSame("menus/{$this->restaurant->id}/en/$snapshot->hash.json", $snapshot->path);
        $this->assertSame(1, $snapshot->dish_count);
        $this->disk->assertExists($snapshot->path);
        $this->assertSame($first->getContent(), $this->disk->get($snapshot->path));

        // from the file, not built again
        $this->disk->put($snapshot->path, gzencode('{"data":[{"title":"From the file"}]}'));

        $this->assertSame(['From the file'], array_column($this->dataOf($this->dishes()), 'title'));
        $this->assertSame(1, MenuSnapshot::query()->count());

        // clients, which don't take gzip
        $plain = $this->dishes(headers: ['Accept-Encoding' => 'identity'])->assertHeaderMissing('Content-Encoding');
        $this->assertSame('From the file', json_decode($plain->getContent(), true)['data'][0]['title']);

        // Cloud Storage gives the file unzipped to its client: it's zipped again
        $this->disk->put($snapshot->path, '{"data":[{"title":"Unzipped"}]}');

        $response = $this->dishes()->assertHeader('Content-Encoding', 'gzip');
        $this->assertSame(['Unzipped'], array_column($this->dataOf($response), 'title'));
    }

    /**
     * Test that hidden and archived dishes and sizes, and dishes of hidden categories and menus,
     * are never in the snapshot, whoever asks for it.
     *
     * @return void
     */
    public function testOnlyWhatGuestsSeeIsThere()
    {
        Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['is_hidden' => true]);
        Dish::factory()->withMenu($this->menu)->withCategory($this->category)->create(['archived' => true]);
        $hiddenCategory = DishCategory::factory()->withMenu($this->menu)->create(['is_hidden' => true]);
        Dish::factory()->withMenu($this->menu)->withCategory($hiddenCategory)->create();
        $hiddenMenu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['is_hidden' => true]);
        $categoryOfHiddenMenu = DishCategory::factory()->withMenu($hiddenMenu)->create();
        Dish::factory()->withMenu($hiddenMenu)->withCategory($categoryOfHiddenMenu)->create();
        $this->dish->sizes()->create(['price' => 99, 'is_hidden' => true]);

        // staff asking for archived ones get what guests get too
        Sanctum::actingAs(User::factory()->withRole(UserRole::fromValue(UserRole::Admin))->create(), ['*']);

        $data = $this->dataOf($this->dishes(query: ['archived' => 'with'])->assertOk());

        $this->assertSame([$this->dish->id], array_column($data, 'id'));
        $this->assertNotContains(99, array_column($data[0]['variants'], 'price'));
    }

    /**
     * Test that a change of a dish gets a new snapshot of a new file, and a change, which doesn't
     * touch dishes, a new snapshot of the same file.
     *
     * @return void
     */
    public function testChangesGetNewSnapshots()
    {
        $this->dishes();
        $first = $this->latestSnapshot();

        // a dish (e.g. in the admin panel)
        $this->dish->update(['title' => 'Green borscht']);
        $this->assertSame(['Green borscht'], array_column($this->dataOf($this->dishes()), 'title'));

        $second = $this->latestSnapshot();
        $this->assertGreaterThan($first->content_version, $second->content_version);
        $this->assertNotSame($first->path, $second->path);

        // a size
        $this->dish->sizes()->first()->update(['price' => 205]);
        $this->assertContains(205, array_column($this->dataOf($this->dishes())[0]['variants'], 'price'));

        // the restaurant's phone: the dishes are the same
        $this->restaurant->update(['phone' => '+380441234567']);
        $this->dishes();

        /** @var Collection<int, MenuSnapshot> $snapshots */
        $snapshots = MenuSnapshot::query()->orderBy('id')->get();
        $this->assertCount(4, $snapshots);
        $this->assertSame($snapshots[2]->path, $snapshots[3]->path);
        $this->assertNotSame($snapshots[2]->content_version, $snapshots[3]->content_version);
    }

    /**
     * Test that the answer to the current snapshot's address is cached for long, the others are
     * checked again by their ETag.
     *
     * @return void
     */
    public function testCurrentSnapshotIsCachedForLong()
    {
        $this->dishes()->assertHeader('Cache-Control', 'no-cache, private');

        $snapshot = $this->latestSnapshot();
        $etag = "\"$snapshot->hash\"";

        $this->dishes(query: ['hash' => $snapshot->hash])
            ->assertOk()
            ->assertHeader('ETag', $etag)
            ->assertHeader('Cache-Control', 'immutable, max-age=31536000, public');

        $this->dishes(headers: ['If-None-Match' => $etag])->assertStatus(304);

        // an old address gets the current snapshot, checked again every time
        $this->dishes(query: ['hash' => str_repeat('0', 40)])
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-cache, private');
    }

    /**
     * Test that clearing the restaurant's website cache (on every save in the editor, a version going
     * live, a change of a menu or a category) moves its dishes to a new snapshot.
     *
     * @return void
     */
    public function testClearingTheWebsiteCacheMovesToANewSnapshot()
    {
        $this->dishes();
        $version = $this->restaurant->fresh()->content_version;

        WebCacheHelper::forgetRestaurants($this->restaurant->id);
        $this->assertSame($version + 1, $this->restaurant->fresh()->content_version);

        $this->category->update(['title' => 'Soups']);
        $this->assertSame($version + 2, $this->restaurant->fresh()->content_version);

        $this->dishes();
        $this->assertSame($version + 2, $this->latestSnapshot()->content_version);
    }

    /**
     * Test that every language has its snapshot.
     *
     * @return void
     */
    public function testLanguagesHaveTheirSnapshots()
    {
        $this->dish->setTranslation('title', 'uk', 'Борщ')->save();

        $this->assertSame(['Borscht'], array_column($this->dataOf($this->dishes('en')), 'title'));
        $this->assertSame(['Борщ'], array_column($this->dataOf($this->dishes('uk')), 'title'));
        $this->assertSame(['en', 'uk'], MenuSnapshot::query()->orderBy('locale')->pluck('locale')->all());

        $this->dishes('de')->assertUnprocessable();
        $this->get('/api/restaurants/999/dishes?locale=en')->assertNotFound();
    }

    /**
     * Test that a restaurant with more dishes than an API page has gets all of them.
     *
     * @return void
     */
    public function testAllDishesAreThere()
    {
        Dish::withoutEvents(fn () => Dish::factory()->count(550)
            ->withMenu($this->menu)
            ->withCategory($this->category)
            ->create());
        WebCacheHelper::forgetRestaurants($this->restaurant->id);

        $this->assertCount(551, $this->dataOf($this->dishes()));
    }

    /**
     * Test that the restaurant's pages say where their dishes are loaded from: the API (with the
     * current snapshot's hash, once it's built), or the bucket, when it lets pages load from it.
     *
     * @return void
     */
    public function testPagesSayWhereTheDishesAre()
    {
        $url = route('web.restaurant.preview', ['locale' => 'en', 'restaurant_id' => $this->restaurant->id]);
        $page = fn () => $this->get($url)->assertOk()->viewData('dishes_url');

        $this->assertSame("/api/restaurants/{$this->restaurant->id}/dishes?locale=en", $page());

        $this->dishes();
        $snapshot = $this->latestSnapshot();

        $this->assertSame("/api/restaurants/{$this->restaurant->id}/dishes?locale=en&hash=$snapshot->hash", $page());

        config(['menu_snapshots.direct' => true]);

        $this->assertSame($this->disk->url($snapshot->path), $page());
    }

    /**
     * Test that snapshots of earlier versions go a day after, and their files, which no snapshot
     * is of anymore.
     *
     * @return void
     */
    public function testOldSnapshotsGo()
    {
        $this->dishes();
        $kept = $this->latestSnapshot();

        $this->dish->update(['title' => 'Green borscht']);
        $this->dishes();
        $old = $this->latestSnapshot();

        $this->travel(25)->hours();
        $this->dish->update(['title' => 'Borscht']);
        $this->dishes();

        // the first one's file is the current one's again, the second one's goes
        $this->assertSame([$kept->path], MenuSnapshot::query()->pluck('path')->unique()->values()->all());
        $this->disk->assertExists($kept->path);
        $this->disk->assertMissing($old->path);
        $this->assertSame(1, MenuSnapshot::query()->count());
        $this->assertTrue(Carbon::now()->isAfter($kept->created_at));
    }

    /**
     * A photo of the dish.
     *
     * @return Media
     */
    protected function photoOfTheDish(): Media
    {
        /** @var Media $photo */
        $photo = MediaFactory::new()->create(['extension' => 'image/jpeg']);

        DB::table('mediables')->insert([
            'media_id' => $photo->id,
            'mediable_id' => $this->dish->id,
            'mediable_type' => $this->dish->getMorphClass(),
            'order' => 1,
        ]);

        return $photo;
    }

    /**
     * A smaller copy (WebP) of the photo.
     *
     * @param Media $photo
     *
     * @return Media
     */
    protected function webpOf(Media $photo): Media
    {
        /** @var Media $webp */
        $webp = MediaFactory::new()->create(['original_id' => $photo->id, 'extension' => 'image/webp']);

        return $webp;
    }

    /**
     * Test that the dishes' photos come with their smaller copies, which pages show.
     *
     * @return void
     */
    public function testPhotosHaveTheirWebPs()
    {
        $photo = $this->photoOfTheDish();
        $webp = $this->webpOf($photo);

        $media = $this->dataOf($this->dishes()->assertOk())[0]['media'];

        $this->assertSame([$photo->id], array_column($media, 'id'));
        $this->assertSame([$webp->id], array_column($media[0]['variants'], 'id'));
        $this->assertSame('webp', $media[0]['variants'][0]['extension']);
    }

    /**
     * Test that a copy of a photo made after its upload gets a new snapshot, which has it,
     * and a photo, which nothing shows, doesn't.
     *
     * @return void
     */
    public function testWebPMadeLaterGetsANewSnapshot()
    {
        $photo = $this->photoOfTheDish();

        $this->assertSame([], $this->dataOf($this->dishes())[0]['media'][0]['variants']);
        $version = $this->restaurant->fresh()->content_version;

        $webp = $this->webpOf($photo);

        $this->assertSame($version + 1, $this->restaurant->fresh()->content_version);
        $this->assertSame([$webp->id], array_column($this->dataOf($this->dishes())[0]['media'][0]['variants'], 'id'));

        $this->webpOf(MediaFactory::new()->create(['extension' => 'image/jpeg']));
        $this->assertSame($version + 1, $this->restaurant->fresh()->content_version);
    }
}
