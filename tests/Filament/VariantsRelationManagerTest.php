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
     * Test that the dish's variants are listed, archived ones too,
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
            ->assertTableColumnStateSet('archived', false, $archived)
            ->filterTable('trashed', false)
            ->assertCanSeeTableRecords([$deleted])
            ->assertCanNotSeeTableRecords([$small, $archived])
            ->callTableAction(RestoreAction::class, $deleted);

        $this->assertFalse($deleted->fresh()->trashed());
    }

    /**
     * Test that variants can be added, edited and deleted from the dish page.
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
        $variant = DishVariant::query()->sole();
        $this->assertSame($this->dish->id, $variant->dish_id);
        $this->assertEquals(180, $variant->price);
        $this->assertFalse((bool) $variant->archived);

        $this->variants()
            ->callTableAction(EditAction::class, $variant, data: ['price' => 190, 'live' => false])
            ->assertHasNoTableActionErrors()
            ->callTableAction(DeleteAction::class, $variant);

        $variant = DishVariant::query()->withoutGlobalScopes()->findOrFail($variant->id);
        $this->assertEquals(190, $variant->price);
        $this->assertTrue((bool) $variant->archived);
        $this->assertTrue($variant->trashed());
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

        $this->assertSame(0, DishVariant::query()->withoutGlobalScopes()->count());
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
