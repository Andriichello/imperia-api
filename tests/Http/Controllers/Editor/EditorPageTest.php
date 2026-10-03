<?php

namespace Tests\Http\Controllers\Editor;

use App\Enums\UserRole;
use App\Models\DishMenu;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Testing\TestResponse;

/**
 * Class EditorPageTest.
 *
 * The editor's page, a page of the restaurant admin.
 */
class EditorPageTest extends EditorTestCase
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
     * Open the editor of the restaurant as the user (signed in with the session).
     *
     * @param User|null $user
     * @param int|null $id
     *
     * @return TestResponse
     */
    protected function openEditor(?User $user, ?int $id = null): TestResponse
    {
        if ($user) {
            $this->signIn($user);
        }

        return $this->get(route('admin.editor', ['id' => $id ?? $this->restaurant->id]));
    }

    /**
     * Sign in to the admin panel as the user, instead of the one, who was signed in before
     * (the panel signs out a user, whose password differs from the one in the session).
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
     * Test that the restaurant's admin gets the editor with everything of the restaurant.
     *
     * @return void
     */
    public function testAdminOpensTheEditor()
    {
        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create(['is_hidden' => true]);
        Restaurant::factory()->create();

        $response = $this->openEditor($this->admin)
            ->assertOk()
            ->assertViewIs('admin.app')
            ->assertViewHas('page', 'editor')
            ->assertSee('<title>Smak · Page editor</title>', false);

        $props = $response->viewData('props');

        $this->assertSame('en', $props['locale']);
        $this->assertSame($this->restaurant->id, $props['restaurant']->resolve()['id']);
        $this->assertSame([$menu->id], collect($props['restaurant']->resolve()['menus'])->pluck('id')->all());
        $this->assertSame([$this->restaurant->id], $props['restaurants']->pluck('id')->all());
        $this->assertSame($this->admin->id, $props['user']['id']);
        $this->assertSame($this->admin->email, $props['user']['email']);
        $this->assertSame(route('admin.dashboard'), $props['urls']['dashboard']);
        $this->assertSame(route('admin.logout'), $props['urls']['logout']);

        // the editor is in the browser's language
        $this->withHeader('Accept-Language', 'uk-UA,uk;q=0.9,en;q=0.8')
            ->get(route('admin.editor', ['id' => $this->restaurant->id]))
            ->assertViewHas('props', fn (array $props) => $props['locale'] === 'uk');
    }

    /**
     * Test that guests are sent to the admin's sign-in, others can't open the editor.
     *
     * @return void
     */
    public function testOnlyAdminsOfTheRestaurantOpenTheEditor()
    {
        // users are created first: the panel's requests switch the default guard to its one
        $others = [
            $this->user(UserRole::Admin, Restaurant::factory()->create()),
            $this->user(UserRole::Manager, $this->restaurant),
            $this->user(UserRole::Customer),
        ];

        $this->openEditor(null)
            ->assertRedirect(route('admin.login'));

        foreach ($others as $user) {
            $this->openEditor($user)->assertForbidden();
        }

        $this->openEditor($this->admin, 999)->assertNotFound();
    }

    /**
     * Test that the editor without a restaurant opens the user's one, or the first one.
     *
     * @return void
     */
    public function testEditorOpensTheUsersRestaurant()
    {
        $other = Restaurant::factory()->create();
        $otherAdmin = $this->user(UserRole::Admin, $other);
        $superAdmin = $this->user(UserRole::Admin);
        $manager = $this->user(UserRole::Manager, $other);

        $this->signIn($otherAdmin)
            ->get(route('admin.editor.index'))
            ->assertRedirect(route('admin.editor', ['id' => $other->id]));

        $this->signIn($superAdmin)
            ->get(route('admin.editor.index'))
            ->assertRedirect(route('admin.editor', ['id' => $this->restaurant->id]));

        $this->get(route('admin.editor', ['id' => $other->id]))
            ->assertOk();

        // the page and the part to edit go along (links of the dashboard)
        $this->get(route('admin.editor.index', ['select' => 'hours', 'page' => 'menu:3', 'other' => 1]))
            ->assertRedirect(route('admin.editor', ['id' => $other->id, 'page' => 'menu:3', 'select' => 'hours']));

        $this->signIn($manager)
            ->get(route('admin.editor.index'))
            ->assertForbidden();
    }
}
