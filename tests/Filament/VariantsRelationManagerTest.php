<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\RelationManagers\VariantsRelationManager;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Restaurant;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\RestoreAction;
use Livewire\Livewire;

/**
 * Class VariantsRelationManagerTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VariantsRelationManagerTest extends FilamentTestCase
{
    /**
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * @var Dish
     */
    protected Dish $dish;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = Restaurant::factory()->create(['currency' => 'EUR']);

        $menu = DishMenu::factory()->withRestaurant($this->restaurant)->create();
        $this->dish = Dish::factory()->withMenu($menu)->create(['title' => 'Borscht']);
    }

    /**
     * Test the relation manager of the dish's edit page.
     *
     * @return mixed
     */
    protected function variants(): mixed
    {
        return Livewire::test(VariantsRelationManager::class, [
            'ownerRecord' => $this->dish,
            'pageClass' => EditDish::class,
        ]);
    }

    /**
     * Test that the dish's sizes are listed, hidden and archived ones too,
     * deleted ones only with the trashed filter.
     *
     * @return void
     */
    public function testListsTheDishVariants()
    {
        $small = DishVariant::factory()->withDish($this->dish)
            ->create(['price' => 100, 'weight' => '300', 'weight_unit' => 'g']);
        $archived = DishVariant::factory()->withDish($this->dish)->create(['archived' => true]);
        $deleted = DishVariant::factory()->withDish($this->dish)->create();
        $deleted->delete();

        $otherDish = Dish::factory()->withMenu($this->dish->menu)->create();
        $other = DishVariant::factory()->withDish($otherDish)->create();

        $this->actingAsStaff();

        $this->variants()
            ->assertCanSeeTableRecords([$small, $archived])
            ->assertCanNotSeeTableRecords([$deleted, $other])
            ->assertTableColumnFormattedStateSet('price', '€100.00', $small)
            ->assertTableColumnStateSet('weight', '300 g', $small)
            ->assertTableColumnStateSet('is_hidden', true, $small)
            ->assertTableColumnStateSet('archived', true, $archived)
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$deleted])
            ->assertCanNotSeeTableRecords([$small, $archived])
            ->callTableAction(RestoreAction::class, $deleted);

        $this->assertFalse($deleted->fresh()->trashed());
    }

    /**
     * Test that sizes can be added, edited (hidden) and deleted from the dish page.
     *
     * @return void
     */
    public function testVariantsCanBeManaged()
    {
        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->variants()
            ->callTableAction(CreateAction::class, data: ['price' => 180, 'weight' => '500', 'weight_unit' => 'g'])
            ->assertHasNoTableActionErrors();

        /** @var DishVariant $variant */
        $variant = DishVariant::query()->where('price', 180)->sole();
        $this->assertSame($this->dish->id, $variant->dish_id);
        $this->assertFalse($variant->is_hidden);
        $this->assertFalse($variant->archived);

        $this->variants()
            ->callTableAction(EditAction::class, $variant, data: ['price' => 190, 'live' => false])
            ->assertHasNoTableActionErrors()
            ->callTableAction(DeleteAction::class, $variant);

        $variant = DishVariant::query()->withoutGlobalScopes()->findOrFail($variant->id);
        $this->assertEquals(190, $variant->price);
        $this->assertTrue($variant->is_hidden);
        $this->assertTrue($variant->trashed());
    }

    /**
     * Test that a size can be archived and restored from the archive.
     *
     * @return void
     */
    public function testSizesCanBeArchived()
    {
        $variant = DishVariant::factory()->withDish($this->dish)->create(['price' => 150]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->variants()->callTableAction('archive', $variant);

        $this->assertTrue($variant->fresh()->archived);
        $this->assertNotNull($variant->fresh()->archived_at);

        // keys, so the records are loaded again
        $this->variants()
            ->assertTableActionHidden('archive', $variant->getKey())
            ->callTableAction('unarchive', $variant->getKey());

        $this->assertFalse($variant->fresh()->archived);
    }

    /**
     * Test that the dish's only size guests see can't be hidden, archived or deleted.
     *
     * @return void
     */
    public function testTheLastShownSizeStays()
    {
        /** @var DishVariant $first */
        $first = $this->dish->sizes()->sole();
        $hidden = DishVariant::factory()->withDish($this->dish)->create(['is_hidden' => true]);

        $this->actingAsStaff(UserRole::Admin, $this->restaurant);

        $this->variants()
            ->assertTableActionHidden(DeleteAction::class, $first)
            ->assertTableActionHidden('archive', $first)
            ->assertTableActionVisible(DeleteAction::class, $hidden);

        $this->assertTrue($first->isLastShown());
        $this->assertFalse($hidden->isLastShown());
    }

    /**
     * Test that negative prices are rejected.
     *
     * @return void
     */
    public function testNegativePricesAreRejected()
    {
        $this->actingAsStaff();

        $this->variants()
            ->callTableAction(CreateAction::class, data: ['price' => -1])
            ->assertHasTableActionErrors(['price' => 'min']);

        // only the dish's first size
        $this->assertSame(1, DishVariant::query()->withoutGlobalScopes()->count());
    }

    /**
     * Test that managers can only see the variants.
     *
     * @return void
     */
    public function testManagersCanOnlyView()
    {
        $variant = DishVariant::factory()->withDish($this->dish)->create();

        $this->actingAsStaff(UserRole::Manager, $this->restaurant);

        $this->variants()
            ->assertCanSeeTableRecords([$variant])
            ->assertTableActionHidden(CreateAction::class)
            ->assertTableActionHidden(EditAction::class, $variant)
            ->assertTableActionHidden(DeleteAction::class, $variant);
    }
}
