<?php

namespace Tests\Filament;

use App\Filament\RelationManagers\AlterationsRelationManager;
use App\Filament\Resources\AlterationResource\Pages\ListAlterations;
use App\Filament\Resources\DishResource\Pages\EditDish;
use App\Filament\Resources\DishResource\Pages\ListDishes;
use App\Filament\Resources\RestaurantResource\Pages\EditRestaurant;
use App\Filament\Resources\RestaurantResource\RelationManagers\SchedulesRelationManager;
use App\Filament\Widgets\PendingChanges;
use App\Models\Dish;
use App\Models\DishMenu;
use App\Models\Morphs\Alteration;
use App\Models\Restaurant;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Actions\CreateAction;
use Livewire\Livewire;

/**
 * Class DateTimeFormatsTest.
 *
 * Readable dates and 24-hour times in the admin, picked with Filament's own picker.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DateTimeFormatsTest extends FilamentTestCase
{
    /**
     * Test that the pickers aren't the browser's and show readable dates and 24-hour times.
     *
     * @return void
     */
    public function testPickersAreReadableAndUse24Hours()
    {
        $restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);
        $dish = Dish::factory()
            ->withMenu(DishMenu::factory()->withRestaurant($restaurant)->create())
            ->create();

        $this->actingAsStaff();

        // a date and time keeps the calendar open after picking the date, for the time
        Livewire::test(AlterationsRelationManager::class, ['ownerRecord' => $dish, 'pageClass' => EditDish::class])
            ->mountTableAction('schedule')
            ->assertFormFieldExists('perform_at', 'mountedTableActionForm', fn (DateTimePicker $field) => !$field
                ->isNative()
                && $field->getDisplayFormat() === 'D, j M Y H:i'
                && $field->getFirstDayOfWeek() === 1
                && !$field->shouldCloseOnDateSelection());

        Livewire::test(ListAlterations::class)
            ->assertFormFieldExists('perform_at.from', 'tableFiltersForm', fn (DatePicker $field) => !$field
                ->isNative()
                && $field->getDisplayFormat() === 'D, j M Y'
                && $field->shouldCloseOnDateSelection());

        Livewire::test(SchedulesRelationManager::class, [
            'ownerRecord' => $restaurant,
            'pageClass' => EditRestaurant::class,
        ])
            ->mountTableAction(CreateAction::class)
            ->assertFormFieldExists('opens_at', 'mountedTableActionForm', fn (TimePicker $field) => !$field
                ->isNative()
                && $field->getDisplayFormat() === 'H:i');
    }

    /**
     * Test that tables show readable dates and 24-hour times.
     *
     * @return void
     */
    public function testTablesShowReadableDates()
    {
        $restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);
        $menu = DishMenu::factory()->withRestaurant($restaurant)->create();
        $dish = Dish::factory()->withMenu($menu)->create(['created_at' => '2026-10-02 15:04:00']);

        $alteration = Alteration::factory()
            ->withModel($dish)
            ->withValues(['price' => 120])
            ->performAt(Carbon::parse('2026-10-04 21:00:00'))
            ->create();

        $this->actingAsStaff();

        Livewire::test(ListDishes::class)
            ->assertTableColumnFormattedStateSet('created_at', '2 Oct 2026, 15:04', $dish);

        // in the restaurant's timezone (UTC+3)
        Livewire::test(PendingChanges::class)
            ->assertTableColumnFormattedStateSet('perform_at', 'Mon, 5 Oct 2026, 00:00', $alteration);
    }
}
