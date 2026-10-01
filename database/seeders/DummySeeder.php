<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\Weekday;
use App\Enums\WeightUnit;
use App\Models\Holiday;
use App\Models\DishMenu;
use App\Models\DishCategory;
use App\Models\Dish;
use App\Models\DishVariant;
use App\Models\Restaurant;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class DummySeeder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DummySeeder extends Seeder
{
    /**
     * Seed the database for testing.
     *
     * @return void
     */
    public function run(): void
    {
        $this->seedRestaurants();
        $this->seedSchedules();
        $this->seedHolidays();
        $this->seedUsers();
        $this->seedProducts();
    }

    /**
     * Seed restaurants.
     *
     * @return void
     */
    public function seedRestaurants(): void
    {
        Restaurant::factory()
            ->withSlug('first')
            ->create([
                'name' => 'First',
                'country' => 'Ukraine',
                'city' => 'Mynai',
                'place' => 'Vul. Kozatsʹka, 2',
                'popularity' => 3,
            ]);

        Restaurant::factory()
            ->withSlug('second')
            ->create([
                'name' => 'Second',
                'country' => 'Ukraine',
                'city' => 'Uzhhorod',
                'place' => 'Sobranetsʹka St, 179А',
                'popularity' => 2,
            ]);
    }

    /**
     * Seed schedules.
     *
     * @return void
     */
    public function seedSchedules(): void
    {
        Restaurant::query()
            // @phpstan-ignore-next-line
            ->each(function (Restaurant $restaurant) {
                foreach (Weekday::getValues() as $weekday) {
                    Schedule::factory()
                        ->withRestaurant($restaurant)
                        ->withWeekday($weekday)
                        ->withRestaurant($restaurant)
                        ->create(['beg_hour' => 6, 'end_hour' => 22]);
                }
            });
    }

    /**
     * Seed schedules.
     *
     * @return void
     */
    public function seedHolidays(): void
    {
        $dates = [
            now()->setMonths(1)->setDay(1),
            now()->setMonths(3)->setDay(8),
            now()->setMonths(8)->setDay(24),
            now()->setMonths(12)->setDay(31),
        ];

        Restaurant::query()
            // @phpstan-ignore-next-line
            ->each(function (Restaurant $restaurant) use ($dates) {
                foreach ($dates as $date) {
                    Holiday::factory()
                        ->withRestaurant($restaurant)
                        ->withDate($date)
                        ->create();
                }
            });
    }

    /**
     * Seed users.
     *
     * @return void
     */
    public function seedUsers(): void
    {
        Restaurant::query()
            // @phpstan-ignore-next-line
            ->each(function (Restaurant $restaurant) {
                User::factory()
                    ->withRole(UserRole::Admin())
                    ->withRestaurant($restaurant)
                    ->create([
                        'name' => $restaurant->name . ' Admin',
                        'email' => $restaurant->slug . '-admin@email.com',
                        'password' => 'pa$$w0rd',
                        'remember_token' => 'admin',
                        'metadata' => json_encode([
                            'isPreviewOnly' => true,
                        ])
                    ]);
                User::factory()
                    ->withRole(UserRole::Manager())
                    ->withRestaurant($restaurant)
                    ->create([
                        'name' => $restaurant->name . ' Manager',
                        'email' => $restaurant->slug . '-manager@email.com',
                        'password' => 'pa$$w0rd',
                        'remember_token' => 'manager',
                        'metadata' => json_encode([
                            'isPreviewOnly' => true,
                        ])
                    ]);
            });
    }

    /**
     * Seed products.
     *
     * @return void
     */
    public function seedProducts(): void
    {
        $restaurant = Restaurant::query()
            ->where('slug', 'first')
            ->firstOrFail();

        $kitchen = DishMenu::factory()
            ->withRestaurant($restaurant)
            ->create([
                'title' => 'Kitchen',
                'description' => null,
            ]);

        $this->seedPizza($kitchen);
        $this->seedSoups($kitchen);
        $this->seedDesserts($kitchen);

        $bar = DishMenu::factory()
            ->withRestaurant($restaurant)
            ->create([
                'title' => 'Bar',
                'description' => null,
            ]);

        $this->seedCocktails($bar);
    }

    /**
     * Seed pizza.
     *
     * @param DishMenu $kitchen
     *
     * @return void
     */
    public function seedPizza(DishMenu $kitchen): void
    {
        $pizzaCategory = DishCategory::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'slug' => 'pizza',
                'title' => 'Pizza',
                'description' => null,
            ]);

        $dish = Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $pizzaCategory->id,
                'title' => 'Margarita',
                'description' => 'The simplest and probably most iconic Italian pizza.'
                    . ' Ingredients: dough, mozzarella, tomato paste, basil, oregano.',
                'price' => 125,
                'weight' => 28,
                'weight_unit' => WeightUnit::Centimeter,
            ]);

        DishVariant::factory()
            ->withDish($dish)
            ->create([
                'price' => 200,
                'weight' => 36,
                'weight_unit' => WeightUnit::Centimeter,
            ]);

        DishVariant::factory()
            ->withDish($dish)
            ->create([
                'price' => 295,
                'weight' => 42,
                'weight_unit' => WeightUnit::Centimeter,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $pizzaCategory->id,
                'title' => 'Romana',
                'description' => 'Ingredients: dough, mozzarella, ham, tomato paste, arugula.',
                'price' => 130,
                'weight' => 420,
                'weight_unit' => WeightUnit::Gram,
            ]);

        $dish = Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $pizzaCategory->id,
                'title' => 'Four Cheese',
                'description' => 'Ingredients: dough, tomato sauce, mozzarella, gorgonzola'
                    . ', Parmigiano Reggiano, goat cheese',
                'price' => 160,
                'weight' => 28,
                'weight_unit' => WeightUnit::Centimeter,
            ]);

        DishVariant::factory()
            ->withDish($dish)
            ->create([
                'price' => 300,
                'weight' => 40,
                'weight_unit' => WeightUnit::Centimeter,
            ]);
    }

    /**
     * Seed soups.
     *
     * @param DishMenu $kitchen
     *
     * @return void
     */
    public function seedSoups(DishMenu $kitchen): void
    {
        $soupsCategory = DishCategory::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'slug' => 'soups',
                'title' => 'Soups',
                'description' => null,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $soupsCategory->id,
                'title' => 'Tomato Soup',
                'price' => 80,
                'weight' => 300,
                'weight_unit' => WeightUnit::Gram,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $soupsCategory->id,
                'title' => 'Celery Soup',
                'price' => 95,
                'weight' => 350,
                'weight_unit' => WeightUnit::Gram,
            ]);
    }

    /**
     * Seed desserts.
     *
     * @param DishMenu $kitchen
     *
     * @return void
     */
    public function seedDesserts(DishMenu $kitchen): void
    {
        $dessertsCategory = DishCategory::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'slug' => 'desserts',
                'title' => 'Desserts',
                'description' => null,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $dessertsCategory->id,
                'title' => 'Tiramisu',
                'price' => 75,
                'weight' => 150,
                'weight_unit' => WeightUnit::Gram,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $kitchen->id,
                'category_id' => $dessertsCategory->id,
                'title' => 'Panna Cotta',
                'price' => 60,
                'weight' => 120,
                'weight_unit' => WeightUnit::Gram,
            ]);
    }

    /**
     * Seed cocktails.
     *
     * @param DishMenu $bar
     *
     * @return void
     */
    public function seedCocktails(DishMenu $bar): void
    {
        $alcoholicCategory = DishCategory::factory()
            ->create([
                'menu_id' => $bar->id,
                'slug' => 'alcoholic',
                'title' => 'Alcoholic',
                'description' => null,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $bar->id,
                'category_id' => $alcoholicCategory->id,
                'title' => 'Martini',
                'price' => 85,
                'weight' => 120,
                'weight_unit' => WeightUnit::Milliliter,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $bar->id,
                'category_id' => $alcoholicCategory->id,
                'title' => 'Pear Mimosa',
                'description' => 'Champagne and pear nectar combine in a delicate drink.',
                'price' => 72,
                'weight' => 170,
                'weight_unit' => WeightUnit::Milliliter,
            ]);

        $nonalcoholicCategory = DishCategory::factory()
            ->create([
                'menu_id' => $bar->id,
                'slug' => 'non-alcoholic',
                'title' => 'Non-alcoholic',
                'description' => null,
            ]);

        $dish = Dish::factory()
            ->create([
                'menu_id' => $bar->id,
                'category_id' => $nonalcoholicCategory->id,
                'title' => 'Mojito',
                'description' => 'Iced Sprite with mint, lime and lemon.',
                'price' => 45,
                'weight' => 250,
                'weight_unit' => WeightUnit::Milliliter,
            ]);

        DishVariant::factory()
            ->withDish($dish)
            ->create([
                'price' => 70,
                'weight' => 400,
                'weight_unit' => WeightUnit::Milliliter,
            ]);

        Dish::factory()
            ->create([
                'menu_id' => $bar->id,
                'category_id' => $nonalcoholicCategory->id,
                'title' => 'Iced Tea With Plums and Thyme',
                'description' => 'Served nonalcoholic fruit-and-herb blend sipper.',
                'price' => 30,
                'weight' => 200,
                'weight_unit' => WeightUnit::Milliliter,
            ]);
    }
}
