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
use App\Models\Morphs\Media;
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

        $this->attachEveryItemToRestaurant('first');
        $this->attachEveryItemToRestaurant('second');
    }

    /**
     * Seed restaurants.
     *
     * @return void
     */
    public function seedRestaurants(): void
    {
        /** @var Media $restaurantMedia */
        $restaurantMedia = Media::query()
            ->folder('/media/defaults/')
            ->name('restaurant.svg')
            ->first();

        $restaurant = Restaurant::factory()
            ->withSlug('first')
            ->create([
                'name' => 'First',
                'country' => 'Ukraine',
                'city' => 'Mynai',
                'place' => 'Vul. Kozatsʹka, 2',
                'popularity' => 3,
            ]);
//        $restaurant->attachMedia($restaurantMedia);

        $restaurant = Restaurant::factory()
            ->withSlug('second')
            ->create([
                'name' => 'Second',
                'country' => 'Ukraine',
                'city' => 'Uzhhorod',
                'place' => 'Sobranetsʹka St, 179А',
                'popularity' => 2,
            ]);
//        $restaurant->attachMedia($restaurantMedia);

        $restaurant = Restaurant::factory()
            ->withSlug('third')
            ->create([
                'name' => 'Third',
                'country' => 'Ukraine',
                'city' => 'Uzhhorod',
                'place' => 'Koryatovycha Square, 1а',
                'popularity' => 1,
            ]);
//        $restaurant->attachMedia($restaurantMedia);
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

        foreach ($dates as $date) {
            Holiday::factory()
                ->withDate($date)
                ->create();
        }
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
                'name' => 'Admin Admins',
                'email' => 'admin@email.com',
                'password' => 'pa$$w0rd',
                'remember_token' => 'admin-remember-token',
            ]);
        User::factory()
            ->withRole(UserRole::Manager())
            ->create([
                'name' => 'Manager Managers',
                'email' => 'manager@email.com',
                'password' => 'pa$$w0rd',
                'remember_token' => 'manager-remember-token',
            ]);
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
        /** @var Media $workdayMedia */
        $workdayMedia = Media::query()
            ->folder('/media/categories/')
            ->name('workday.svg')
            ->first();
        $workdayCategory = Category::factory()->create([
            'slug' => 'work-day-tickets',
            'target' => slugClass(Ticket::class),
            'title' => 'Work Day Tickets',
            'description' => 'Tickets that are available from Monday to Thursday.',
        ]);
//        $workdayCategory->attachMedia($workdayMedia);

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

        /** @var Media $weekendMedia */
        $weekendMedia = Media::query()
            ->folder('/media/categories/')
            ->name('weekend.svg')
            ->first();
        $weekendCategory = Category::factory()->create([
            'slug' => 'weekend-tickets',
            'target' => slugClass(Ticket::class),
            'title' => 'Weekend Tickets',
            'description' => 'Tickets that are available from Friday to Sunday.',
        ]);
//        $weekendCategory->attachMedia($weekendMedia);

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
        /** @var Media $indoorsMedia */
        $indoorsMedia = Media::query()
            ->folder('/media/categories/')
            ->name('indoor.svg')
            ->first();
        $indoorsCategory = Category::factory()->create([
            'slug' => 'indoors',
            'target' => slugClass(Service::class),
            'title' => 'Indoors',
            'description' => null,
        ]);
//        $indoorsCategory->attachMedia($indoorsMedia);

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

        /** @var Media $outdoorsMedia */
        $outdoorsMedia = Media::query()
            ->folder('/media/categories/')
            ->name('outdoor.svg')
            ->first();
        $outdoorsCategory = Category::factory()->create([
            'slug' => 'outdoors',
            'target' => slugClass(Service::class),
            'title' => 'Outdoors',
            'description' => null,
        ]);
//        $outdoorsCategory->attachMedia($outdoorsMedia);

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
        /** @var Media $roomsMedia */
        $roomsMedia = Media::query()
            ->folder('/media/defaults/')
            ->name('door.svg')
            ->first();
        $roomsCategory = Category::factory()->create([
            'slug' => 'rooms',
            'target' => slugClass(Space::class),
            'title' => 'Rooms',
            'description' => null,
        ]);
//        $roomsCategory->attachMedia($roomsMedia);

        /** @var Media $tablesMedia */
        $tablesMedia = Media::query()
            ->folder('/media/defaults/')
            ->name('table.svg')
            ->first();
        $tablesCategory = Category::factory()->create([
            'slug' => 'tables',
            'target' => slugClass(Space::class),
            'title' => 'Tables',
            'description' => null,
        ]);
//        $tablesCategory->attachMedia($tablesMedia);

        for ($i = 1; $i <= 2; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                $table = Space::factory()->create([
                    'title' => "Table #$j($i)",
                    'floor' => $i,
                    'number' => $j,
                    'price' => 0.0,
                ]);
                $table->attachMedia($tablesMedia);
                $table->attachCategories($tablesCategory);

                if ($j <= 3) {
                    $room = Space::factory()->create([
                        'title' => "Room #$j($i)",
                        'floor' => $i,
                        'number' => $j,
                        'price' => rand(1, 10) * 10,
                    ]);
                    $room->attachMedia($roomsMedia);
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
        $kitchen = Menu::factory()->create([
            'title' => 'Kitchen',
            'description' => null,
        ]);
        $this->seedPizza($kitchen);
        $this->seedSoups($kitchen);
        $this->seedDesserts($kitchen);

        $bar = Menu::factory()->create([
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
        /** @var Media $pizzaMedia */
        $pizzaMedia = Media::query()
            ->folder('/media/categories/')
            ->name('pizza.svg')
            ->first();
        $pizzaCategory = Category::factory()->create([
            'slug' => 'pizza',
            'target' => slugClass(Product::class),
            'title' => 'Pizza',
            'description' => null,
        ]);
//        $pizzaCategory->attachMedia($pizzaMedia);

        $product = Product::factory()->create([
            'title' => 'Margarita',
            'description' => 'The simplest and probably most iconic Italian pizza.'
                . ' Ingredients: dough, mozzarella, tomato paste, basil, oregano.',
            'price' => 125,
            'weight' => 28,
            'weight_unit' => WeightUnit::Centimeter,
        ]);
        // $product->attachMedia($pizzaMedia);
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
        ]);
        // $product->attachMedia($pizzaMedia);
        $product->attachCategories($pizzaCategory);
        $product->menus()->attach($kitchen->id);

        $product = Product::factory()->create([
            'title' => 'Four Cheese',
            'description' => 'Ingredients: dough, tomato sauce, mozzarella, gorgonzola'
                . ', Parmigiano Reggiano, goat cheese',
            'price' => 160,
            'weight' => 28,
            'weight_unit' => WeightUnit::Centimeter,
        ]);
        // $product->attachMedia($pizzaMedia);
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
        /** @var Media $soupsMedia */
        $soupsMedia = Media::query()
            ->folder('/media/categories/')
            ->name('soup.svg')
            ->first();
        $soupsCategory = Category::factory()->create([
            'slug' => 'soups',
            'target' => slugClass(Product::class),
            'title' => 'Soups',
            'description' => null,
        ]);
//        $soupsCategory->attachMedia($soupsMedia);

        $product = Product::factory()->create([
            'title' => 'Tomato Soup',
            'price' => 80,
            'weight' => 300,
            'weight_unit' => WeightUnit::Gram,
        ]);
        // $product->attachMedia($soupsMedia);
        $product->attachCategories($soupsCategory);
        $product->menus()->attach($kitchen->id);

        $product = Product::factory()->create([
            'title' => 'Celery Soup',
            'price' => 95,
            'weight' => 350,
            'weight_unit' => WeightUnit::Gram,
        ]);
        // $product->attachMedia($soupsMedia);
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
        /** @var Media $dessertsMedia */
        $dessertsMedia = Media::query()
            ->folder('/media/categories/')
            ->name('croissant.svg')
            ->first();
        $dessertsCategory = Category::factory()->create([
            'slug' => 'desserts',
            'target' => slugClass(Product::class),
            'title' => 'Desserts',
            'description' => null,
        ]);
//        $dessertsCategory->attachMedia($dessertsMedia);

        $product = Product::factory()->create([
            'title' => 'Tiramisu',
            'price' => 75,
            'weight' => 150,
            'weight_unit' => WeightUnit::Gram,
        ]);
        // $product->attachMedia($dessertsMedia);
        $product->attachCategories($dessertsCategory);
        $product->menus()->attach($kitchen->id);

        $product = Product::factory()->create([
            'title' => 'Panna Cotta',
            'price' => 60,
            'weight' => 120,
            'weight_unit' => WeightUnit::Gram,
        ]);
        // $product->attachMedia($dessertsMedia);
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
        /** @var Media $alcoholicMedia */
        $alcoholicMedia = Media::query()
            ->folder('/media/categories/')
            ->name('alcoholic.svg')
            ->first();
        $alcoholicCategory = Category::factory()->create([
            'slug' => 'alcoholic',
            'target' => slugClass(Product::class),
            'title' => 'Alcoholic',
            'description' => null,
        ]);
//        $alcoholicCategory->attachMedia($alcoholicMedia);

        $product = Product::factory()->create([
            'title' => 'Martini',
            'price' => 85,
            'weight' => 120,
            'weight_unit' => WeightUnit::Milliliter,
        ]);
        // $product->attachMedia($alcoholicMedia);
        $product->attachCategories($alcoholicCategory);
        $product->menus()->attach($bar->id);

        $product = Product::factory()->create([
            'title' => 'Pear Mimosa',
            'description' => 'Champagne and pear nectar combine in a delicate drink.',
            'price' => 72,
            'weight' => 170,
            'weight_unit' => WeightUnit::Milliliter,
        ]);
        // $product->attachMedia($alcoholicMedia);
        $product->attachCategories($alcoholicCategory);
        $product->menus()->attach($bar->id);

        /** @var Media $nonalcoholicMedia */
        $nonalcoholicMedia = Media::query()
            ->folder('/media/categories/')
            ->name('non-alcoholic.svg')
            ->first();
        $nonalcoholicCategory = Category::factory()->create([
            'slug' => 'non-alcoholic',
            'target' => slugClass(Product::class),
            'title' => 'Non-alcoholic',
            'description' => null,
        ]);
//        $nonalcoholicCategory->attachMedia($nonalcoholicMedia);

        $product = Product::factory()->create([
            'title' => 'Mojito',
            'description' => 'Iced Sprite with mint, lime and lemon.',
            'price' => 45,
            'weight' => 250,
            'weight_unit' => WeightUnit::Milliliter,
        ]);
        // $product->attachMedia($nonalcoholicMedia);
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
        ]);
        // $product->attachMedia($nonalcoholicMedia);
        $product->attachCategories($nonalcoholicCategory);
        $product->menus()->attach($bar->id);
    }

    /**
     * Attach all tickets, menus, products, spaces, services and holidays
     * to the restaurant with given $slug.
     *-
     * @param string $slug
     *
     * @return void
     */
    public function attachEveryItemToRestaurant(string $slug): void
    {
        /** @var Restaurant $restaurant */
        $restaurant = Restaurant::query()
            ->withSlug($slug)
            ->firstOrFail();

        Menu::query()
            ->each(fn(Menu $item) => $restaurant->menus()->attach($item->id));

        Category::query()
            ->each(fn(Category $item) => $restaurant->categories()->attach($item->id));

        Product::query()
            ->each(fn(Product $item) => $restaurant->products()->attach($item->id));

        Space::query()
            ->each(fn(Space $item) => $restaurant->spaces()->attach($item->id));

        Ticket::query()
            ->each(fn(Ticket $item) => $restaurant->tickets()->attach($item->id));

        Service::query()
            ->each(fn(Service $item) => $restaurant->services()->attach($item->id));

        Holiday::query()
            ->each(fn(Holiday $item) => $restaurant->holidays()->attach($item->id));
    }
}
