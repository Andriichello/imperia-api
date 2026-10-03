<?php

namespace Tests\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\MenuVersion;
use App\Models\MenuVersionChange;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Http\Controllers\Editor\EditorTestCase;

/**
 * Class VersionPageTest.
 *
 * The page of a scheduled version, a page of the restaurant admin.
 */
class VersionPageTest extends EditorTestCase
{
    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // the page's scripts and styles aren't built for tests
        $this->withoutMix();
    }

    /**
     * Sign in as the user, instead of the one, who was signed in before.
     *
     * @param User $user
     *
     * @return static
     */
    protected function signIn(User $user): static
    {
        $this->flushSession();

        return $this->actingAs($user, 'web');
    }

    /**
     * A version of the restaurant with a change of a dish's photos.
     *
     * @param Restaurant $restaurant
     *
     * @return MenuVersion
     */
    protected function version(Restaurant $restaurant): MenuVersion
    {
        $menu = DishMenu::factory()->withRestaurant($restaurant)->create();
        $category = DishCategory::factory()->withMenu($menu)->create();
        /** @var Dish $dish */
        $dish = Dish::factory()->withMenu($menu)->withCategory($category)->create();

        // uploaded for the version: it isn't the dish's photo yet
        Storage::fake(config('media.disk'));
        $photo = $this->postJson("/api/editor/restaurants/$restaurant->id/media", [
            'file' => UploadedFile::fake()->image('borscht.jpg', 800, 600),
        ])->json('data.id');

        $version = MenuVersion::factory()->withRestaurant($restaurant)->scheduled()->create(['name' => 'Winter menu']);

        MenuVersionChange::factory()->inVersion($version)->create([
            'target_type' => 'dishes',
            'target_id' => $dish->id,
            'fields' => ['media' => ['live' => [], 'new' => [['id' => $photo, 'is_hidden' => false]]]],
        ]);

        return $version;
    }

    /**
     * Test that the restaurant's admin gets the version with its changes, their photos, and
     * everything of the restaurant; it becomes the current restaurant.
     *
     * @return void
     */
    public function testAdminOpensTheVersion()
    {
        $version = $this->version($this->restaurant);

        $response = $this->signIn($this->admin)
            ->get(route('admin.versions.show', ['id' => $version->id]))
            ->assertOk()
            ->assertViewIs('admin.app')
            ->assertViewHas('page', 'version')
            ->assertSee('<title>Winter menu · ' . $this->restaurant->name . '</title>', false)
            ->assertSessionHas('admin.restaurant', $this->restaurant->id);

        $props = $response->viewData('props');
        $data = $props['version']->resolve();

        $this->assertSame($version->id, $data['id']);
        $this->assertCount(1, $data['changes']);
        $this->assertCount(1, $data['media']);
        $this->assertSame($this->restaurant->id, $props['restaurant']->resolve()['id']);
        $this->assertSame(url('admin/versions'), $props['urls']['version']);
    }

    /**
     * Test that guests sign in first, others can't open the version.
     *
     * @return void
     */
    public function testOnlyAdminsOfItsRestaurantOpenTheVersion()
    {
        $version = $this->version($this->restaurant);
        $other = Restaurant::factory()->create();
        // users are created first: the panel's requests switch the default guard to its one
        $users = [
            $this->user(UserRole::Admin, $other),
            $this->user(UserRole::Manager, $this->restaurant),
            $this->user(UserRole::Customer),
        ];

        $this->get(route('admin.versions.show', ['id' => $version->id]))
            ->assertRedirect(route('admin.login'));

        foreach ($users as $user) {
            $this->signIn($user)
                ->get(route('admin.versions.show', ['id' => $version->id]))
                ->assertForbidden();
        }

        $this->signIn($this->admin)
            ->get(route('admin.versions.show', ['id' => 999]))
            ->assertNotFound();
    }
}
