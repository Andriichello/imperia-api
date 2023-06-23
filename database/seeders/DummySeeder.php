<?php

namespace Database\Seeders;

use App\Enums\FamilyRelation;
use App\Enums\UserRole;
use App\Enums\Weekday;
use App\Enums\WeightUnit;
use App\Models\Customer;
use App\Models\FamilyMember;
use App\Models\Holiday;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Restaurant;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
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
        $this->seedCustomers();
        $this->seedTickets();
        $this->seedProducts();
        $this->seedServices();
        $this->seedSpaces();
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
        User::factory()
            ->withRole(UserRole::Admin())
            ->create([
                'name' => 'Super',
                'email' => 'super@email.com',
                'password' => 'pa$$w0rd',
                'remember_token' => 'super',
                'metadata' => json_encode([
                    'isPreviewOnly' => false,
                ])
            ]);

        Restaurant::query()
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
     * Seed customers.
     *
     * @return void
     */
    public function seedCustomers(): void
    {
        User::factory()
            ->withCustomer(
                [
                    'name' => $name = 'Customer',
                    'surname' => $surname = 'Customers',
                    'email' => $email = 'customer@email.com',
                    'phone' => '+380501111111',
                    'birthdate' => '1987-06-05',
                ]
            )->create(
                [
                    'name' => "$name $surname",
                    'email' => $email,
                ]
            );

        $john = User::factory()
            ->withCustomer(
                [
                    'name' => $name = 'John',
                    'surname' => $surname = 'Doe',
                    'email' => $email = 'john.doe@email.com',
                    'phone' => '+380502222222',
                    'birthdate' => '1986-01-26',
                ]
            )->create(
                [
                    'name' => "$name $surname",
                    'email' => $email,
                ]
            );

        $john->customer->attachComments(
            'This is the first test customer.',
            'John Doe is a typical fake name.',
        );
        FamilyMember::factory()
            ->withRelative($john->customer, FamilyRelation::Child())
            ->create([
                'name' => 'Jenny Doe',
                'birthdate' => '2010-07-03',
            ]);
        FamilyMember::factory()
            ->withRelative($john->customer, FamilyRelation::Child())
            ->create([
                'name' => 'Tommy Doe',
                'birthdate' => '2013-07-03',
            ]);

        $richard = Customer::factory()
            ->create(
                [
                    'name' => 'Richard',
                    'surname' => 'Jefferson',
                    'email' => 'richard.jefferson@email.com',
                    'phone' => '+380503333333',
                    'birthdate' => '1973-08-03',
                ]
            );

        $richard->attachComments('He played in NBA for Cleveland Cavaliers.');
    }

    /**
     * Seed tickets.
     *
     * @return void
     */
    public function seedTickets(): void
    {
        $workdayCategory = Category::factory()->create([
            'slug' => 'work-day-tickets',
            'target' => slugClass(Ticket::class),
            'title' => 'Work Day Tickets',
            'description' => 'Tickets that are available from Monday to Thursday.',
        ]);

        $ticket = Ticket::factory()->create([
            'title' => 'Child workday ticket',
            'description' => 'Tickets that are available for customers younger than 14 years.',
            'price' => 50,
        ]);
        $ticket->attachCategories($workdayCategory);

        $ticket = Ticket::factory()->create([
            'title' => 'Adult workday ticket',
            'description' => 'Tickets that are available for customers older than 14 years.',
            'price' => 75,
        ]);
        $ticket->attachCategories($workdayCategory);

        $weekendCategory = Category::factory()->create([
            'slug' => 'weekend-tickets',
            'target' => slugClass(Ticket::class),
            'title' => 'Weekend Tickets',
            'description' => 'Tickets that are available from Friday to Sunday.',
        ]);

        $ticket = Ticket::factory()->create([
            'title' => 'Child weekend ticket',
            'description' => 'Tickets that are available for customers younger than 14 years.',
            'price' => 80,
        ]);
        $ticket->attachCategories($weekendCategory);

        $ticket = Ticket::factory()->create([
            'title' => 'Adult weekend ticket',
            'description' => 'Tickets that are available for customers older than 14 years.',
            'price' => 100,
        ]);
        $ticket->attachCategories($weekendCategory);
    }

    /**
     * Seed services.
     *
     * @return void
     */
    public function seedServices(): void
    {
        $indoorsCategory = Category::factory()->create([
            'slug' => 'indoors',
            'target' => slugClass(Service::class),
            'title' => 'Indoors',
            'description' => null,
        ]);

        $service = Service::factory()->create([
            'title' => 'Clown Show',
            'once_paid_price' => 300,
            'hourly_paid_price' => 200,
        ]);
        $service->attachCategories($indoorsCategory);

        $service = Service::factory()->create([
            'title' => 'Fruits Carving',
            'once_paid_price' => 1000,
        ]);
        $service->attachCategories($indoorsCategory);

        $outdoorsCategory = Category::factory()->create([
            'slug' => 'outdoors',
            'target' => slugClass(Service::class),
            'title' => 'Outdoors',
            'description' => null,
        ]);

        $service = Service::factory()->create([
            'title' => 'Fire Show',
            'once_paid_price' => 1200,
            'hourly_paid_price' => 600,
        ]);
        $service->attachCategories($outdoorsCategory);

        $service = Service::factory()->create([
            'title' => 'Magic Show',
            'once_paid_price' => 1000,
            'hourly_paid_price' => 500,
        ]);
        $service->attachCategories($outdoorsCategory);
    }

    /**
     * Seed spaces.
     *
     * @return void
     */
    public function seedSpaces(): void
    {
        $roomsCategory = Category::factory()->create([
            'slug' => 'rooms',
            'target' => slugClass(Space::class),
            'title' => 'Rooms',
            'description' => null,
        ]);

        $tablesCategory = Category::factory()->create([
            'slug' => 'tables',
            'target' => slugClass(Space::class),
            'title' => 'Tables',
            'description' => null,
        ]);

        for ($i = 1; $i <= 2; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                $table = Space::factory()->create([
                    'title' => "Table #$j($i)",
                    'floor' => $i,
                    'number' => $j,
                    'price' => 0.0,
                ]);
                $table->attachCategories($tablesCategory);

                if ($j <= 3) {
                    $room = Space::factory()->create([
                        'title' => "Room #$j($i)",
                        'floor' => $i,
                        'number' => $j,
                        'price' => rand(1, 10) * 10,
                    ]);
                    $room->attachCategories($roomsCategory);
                }
            }
        }
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

        $kitchen = Menu::factory()
            ->withRestaurant($restaurant)
            ->create([
                'title' => 'Kitchen',
                'description' => null,
            ]);

        $this->seedPizza($kitchen);
        $this->seedSoups($kitchen);
        $this->seedDesserts($kitchen);

        $bar = Menu::factory()
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
     * @param Menu $kitchen
     *
     * @return void
     */
    public function seedPizza(Menu $kitchen): void
    {
        $pizzaCategory = Category::factory()->create([
            'slug' => 'pizza',
            'target' => slugClass(Product::class),
            'title' => 'Pizza',
            'description' => null,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);

        $product = Product::factory()->create([
            'title' => 'Margarita',
            'description' => 'The simplest and probably most iconic Italian pizza.'
                . ' Ingredients: dough, mozzarella, tomato paste, basil, oregano.',
            'price' => 125,
            'weight' => 28,
            'weight_unit' => WeightUnit::Centimeter,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($pizzaCategory);
        $product->menus()->attach($kitchen->id);

        ProductVariant::factory()
            ->withProduct($product)
            ->create([
                'price' => 200,
                'weight' => 36,
                'weight_unit' => WeightUnit::Centimeter,
            ]);

        ProductVariant::factory()
            ->withProduct($product)
            ->create([
                'price' => 295,
                'weight' => 42,
                'weight_unit' => WeightUnit::Centimeter,
            ]);

        $product = Product::factory()->create([
            'title' => 'Romana',
            'description' => 'Ingredients: dough, mozzarella, ham, tomato paste, arugula.',
            'price' => 130,
            'weight' => 420,
            'weight_unit' => WeightUnit::Gram,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($pizzaCategory);
        $product->menus()->attach($kitchen->id);

        $product = Product::factory()->create([
            'title' => 'Four Cheese',
            'description' => 'Ingredients: dough, tomato sauce, mozzarella, gorgonzola'
                . ', Parmigiano Reggiano, goat cheese',
            'price' => 160,
            'weight' => 28,
            'weight_unit' => WeightUnit::Centimeter,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($pizzaCategory);
        $product->menus()->attach($kitchen->id);

        ProductVariant::factory()
            ->withProduct($product)
            ->create([
                'price' => 300,
                'weight' => 40,
                'weight_unit' => WeightUnit::Centimeter,
            ]);
    }

    /**
     * Seed soups.
     *
     * @param Menu $kitchen
     *
     * @return void
     */
    public function seedSoups(Menu $kitchen): void
    {
        $soupsCategory = Category::factory()->create([
            'slug' => 'soups',
            'target' => slugClass(Product::class),
            'title' => 'Soups',
            'description' => null,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);

        $product = Product::factory()->create([
            'title' => 'Tomato Soup',
            'price' => 80,
            'weight' => 300,
            'weight_unit' => WeightUnit::Gram,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($soupsCategory);
        $product->menus()->attach($kitchen->id);

        $product = Product::factory()->create([
            'title' => 'Celery Soup',
            'price' => 95,
            'weight' => 350,
            'weight_unit' => WeightUnit::Gram,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($soupsCategory);
        $product->menus()->attach($kitchen->id);
    }

    /**
     * Seed desserts.
     *
     * @param Menu $kitchen
     *
     * @return void
     */
    public function seedDesserts(Menu $kitchen): void
    {
        $dessertsCategory = Category::factory()->create([
            'slug' => 'desserts',
            'target' => slugClass(Product::class),
            'title' => 'Desserts',
            'description' => null,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);

        $product = Product::factory()->create([
            'title' => 'Tiramisu',
            'price' => 75,
            'weight' => 150,
            'weight_unit' => WeightUnit::Gram,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($dessertsCategory);
        $product->menus()->attach($kitchen->id);

        $product = Product::factory()->create([
            'title' => 'Panna Cotta',
            'price' => 60,
            'weight' => 120,
            'weight_unit' => WeightUnit::Gram,
            'restaurant_id' => $kitchen->restaurant_id,
        ]);
        $product->attachCategories($dessertsCategory);
        $product->menus()->attach($kitchen->id);
    }

    /**
     * Seed cocktails.
     *
     * @param Menu $bar
     *
     * @return void
     */
    public function seedCocktails(Menu $bar): void
    {
        $alcoholicCategory = Category::factory()->create([
            'slug' => 'alcoholic',
            'target' => slugClass(Product::class),
            'title' => 'Alcoholic',
            'description' => null,
            'restaurant_id' => $bar->restaurant_id,
        ]);

        $product = Product::factory()->create([
            'title' => 'Martini',
            'price' => 85,
            'weight' => 120,
            'weight_unit' => WeightUnit::Milliliter,
            'restaurant_id' => $bar->restaurant_id,
        ]);
        $product->attachCategories($alcoholicCategory);
        $product->menus()->attach($bar->id);

        $product = Product::factory()->create([
            'title' => 'Pear Mimosa',
            'description' => 'Champagne and pear nectar combine in a delicate drink.',
            'price' => 72,
            'weight' => 170,
            'weight_unit' => WeightUnit::Milliliter,
            'restaurant_id' => $bar->restaurant_id,
        ]);
        $product->attachCategories($alcoholicCategory);
        $product->menus()->attach($bar->id);

        $nonalcoholicCategory = Category::factory()->create([
            'slug' => 'non-alcoholic',
            'target' => slugClass(Product::class),
            'title' => 'Non-alcoholic',
            'description' => null,
            'restaurant_id' => $bar->restaurant_id,
        ]);

        $product = Product::factory()->create([
            'title' => 'Mojito',
            'description' => 'Iced Sprite with mint, lime and lemon.',
            'price' => 45,
            'weight' => 250,
            'weight_unit' => WeightUnit::Milliliter,
            'restaurant_id' => $bar->restaurant_id,
        ]);
        $product->attachCategories($nonalcoholicCategory);
        $product->menus()->attach($bar->id);

        ProductVariant::factory()
            ->withProduct($product)
            ->create([
                'price' => 70,
                'weight' => 400,
                'weight_unit' => WeightUnit::Milliliter,
            ]);

        $product = Product::factory()->create([
            'title' => 'Iced Tea With Plums and Thyme',
            'description' => 'Served nonalcoholic fruit-and-herb blend sipper.',
            'price' => 30,
            'weight' => 200,
            'weight_unit' => WeightUnit::Milliliter,
            'restaurant_id' => $bar->restaurant_id,
        ]);
        $product->attachCategories($nonalcoholicCategory);
        $product->menus()->attach($bar->id);
    }
}
