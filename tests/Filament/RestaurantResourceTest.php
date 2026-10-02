<?php

namespace Tests\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\RestaurantResource\Pages\CreateRestaurant;
use App\Filament\Resources\RestaurantResource\Pages\EditRestaurant;
use App\Filament\Resources\RestaurantResource\Pages\ListRestaurants;
use App\Models\Restaurant;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Livewire\Livewire;

/**
 * Class RestaurantResourceTest.
 */
class RestaurantResourceTest extends FilamentTestCase
{
    /**
     * Get valid form data for a new restaurant.
     *
     * @param array $overrides
     *
     * @return array
     */
    protected function restaurantData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Imperia',
            'slug' => 'imperia',
            'place' => 'Shevchenka St, 1',
            'city' => 'Kyiv',
            'country' => 'Ukraine',
            'timezone' => 'Europe/Kyiv',
            'currency' => 'UAH',
        ], $overrides);
    }

    /**
     * Test that type, currency and language are saved from their selects, and notes with them.
     *
     * @return void
     */
    public function testRestaurantIsCreatedWithSelectedValues()
    {
        $this->actingAsStaff();

        // numbered repeater items, instead of random keys
        $undoRepeaterFake = Repeater::fake();

        Livewire::test(CreateRestaurant::class)
            ->assertFormSet(['timezone' => 'Europe/Kyiv', 'currency' => 'UAH'])
            ->fillForm($this->restaurantData([
                'establishment' => 'cafe',
                'currency' => 'EUR',
                'locale' => 'uk',
                'notes' => [
                    ['text' => 'Pets welcome', 'is_hidden' => false],
                    ['text' => 'Closed for a private event', 'is_hidden' => true],
                ],
            ]))
            ->call('create')
            ->assertHasNoFormErrors();

        $undoRepeaterFake();

        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::query()->where('slug', 'imperia')->sole();

        $this->assertSame('cafe', $restaurant->establishment);
        $this->assertSame('EUR', $restaurant->currency);
        $this->assertSame('uk', $restaurant->locale);
        $this->assertSame(
            ['Pets welcome', 'Closed for a private event'],
            $restaurant->notes->pluck('text')->all()
        );
        $this->assertSame([false, true], $restaurant->notes->pluck('is_hidden')->all());
        $this->assertSame([1, 2], $restaurant->notes->pluck('order')->all());
    }

    /**
     * Test that unknown values and taken slugs are rejected.
     *
     * @return void
     */
    public function testInvalidValuesAreRejected()
    {
        Restaurant::factory()->withSlug('imperia')->create();

        $this->actingAsStaff();

        Livewire::test(CreateRestaurant::class)
            ->fillForm($this->restaurantData([
                'establishment' => 'night club',
                'currency' => 'XYZ',
                'locale' => 'de',
            ]))
            ->call('create')
            ->assertHasFormErrors([
                'slug' => 'unique',
                'establishment' => 'in',
                'currency' => 'in',
                'locale' => 'in',
            ]);
    }

    /**
     * Test that values entered before the selects existed are kept and can still be saved,
     * and that lowercase currency codes are shown in upper case.
     *
     * @return void
     */
    public function testExistingValuesAreKept()
    {
        $restaurant = Restaurant::factory()->withSlug('imperia')->create(['timezone' => 'Europe/Kyiv']);
        $restaurant->establishment = 'Cafe & Bar';
        $restaurant->currency = 'uah';
        $restaurant->save();

        $this->actingAsStaff(UserRole::Admin, $restaurant);

        Livewire::test(EditRestaurant::class, ['record' => $restaurant->getRouteKey()])
            ->assertFormSet(['establishment' => 'Cafe & Bar', 'currency' => 'UAH'])
            ->assertFormFieldExists('establishment', function (Select $field) {
                return ($field->getOptions()['Cafe & Bar'] ?? null) === 'Cafe & Bar'
                    && ($field->getOptions()['bakery'] ?? null) === 'Bakery';
            })
            ->call('save')
            ->assertHasNoFormErrors();

        $restaurant = $restaurant->fresh();
        $this->assertSame('Cafe & Bar', $restaurant->establishment);
        $this->assertSame('UAH', $restaurant->currency);
    }

    /**
     * Test the link to the restaurant's page on the website.
     *
     * @return void
     */
    public function testLinksToTheWebsite()
    {
        $restaurant = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);
        $restaurant->locale = 'uk';
        $restaurant->save();

        $other = Restaurant::factory()->create(['timezone' => 'Europe/Kyiv']);

        $this->actingAsStaff();

        Livewire::test(ListRestaurants::class)
            ->assertTableActionHasUrl('website', url("/uk/web/{$restaurant->id}"), $restaurant)
            ->assertTableActionShouldOpenUrlInNewTab('website', $restaurant)
            // without a language: the default one
            ->assertTableActionHasUrl('website', url("/en/web/{$other->id}"), $other);

        Livewire::test(EditRestaurant::class, ['record' => $restaurant->getRouteKey()])
            ->assertActionHasUrl('website', url("/uk/web/{$restaurant->id}"));
    }
}
