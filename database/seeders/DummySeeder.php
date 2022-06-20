<?php

namespace Database\Seeders;

use App\Enums\FamilyRelation;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\FamilyMember;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Morphs\Media;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class DummySeeder.
 */
class DummySeeder extends Seeder
{
    /**
     * Seed the database for testing.
     *
     * @return void
     */
    public function run()
    {
        $this->seedUsers();
        $this->seedCustomers();
        $this->seedTickets();
        $this->seedProducts();
        $this->seedServices();
        $this->seedSpaces();
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
            ->fromFolder('categories')
            ->where('name', 'workday.svg')
            ->first();
        $workdayCategory = Category::factory()->create([
            'slug' => 'work-day-tickets',
            'target' => slugClass(Ticket::class),
            'title' => 'Work Day Tickets',
            'description' => 'Tickets that are available from Monday to Thursday.',
        ]);
        $workdayCategory->attachMedia($workdayMedia);

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
            ->fromFolder('categories')
            ->where('name', 'weekend.svg')
            ->first();
        $weekendCategory = Category::factory()->create([
            'slug' => 'weekend-tickets',
            'target' => slugClass(Ticket::class),
            'title' => 'Weekend Tickets',
            'description' => 'Tickets that are available from Friday to Sunday.',
        ]);
        $weekendCategory->attachMedia($weekendMedia);

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
            ->fromFolder('categories')
            ->where('name', 'indoor.svg')
            ->first();
        $indoorsCategory = Category::factory()->create([
            'slug' => 'indoors',
            'target' => slugClass(Service::class),
            'title' => 'Indoors',
            'description' => null,
        ]);
        $indoorsCategory->attachMedia($indoorsMedia);

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
            ->fromFolder('categories')
            ->where('name', 'outdoor.svg')
            ->first();
        $outdoorsCategory = Category::factory()->create([
            'slug' => 'outdoors',
            'target' => slugClass(Service::class),
            'title' => 'Outdoors',
            'description' => null,
        ]);
        $outdoorsCategory->attachMedia($outdoorsMedia);

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
            ->fromFolder('defaults')
            ->where('name', 'door.svg')
            ->first();
        $roomsCategory = Category::factory()->create([
            'slug' => 'rooms',
            'target' => slugClass(Space::class),
            'title' => 'Rooms',
            'description' => null,
        ]);
        $roomsCategory->attachMedia($roomsMedia);

        /** @var Media $tablesMedia */
        $tablesMedia = Media::query()
            ->fromFolder('defaults')
            ->where('name', 'table.svg')
            ->first();
        $tablesCategory = Category::factory()->create([
            'slug' => 'tables',
            'target' => slugClass(Space::class),
            'title' => 'Tables',
            'description' => null,
        ]);
        $tablesCategory->attachMedia($tablesMedia);

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
            ->fromFolder('categories')
            ->where('name', 'pizza.svg')
            ->first();
        $pizzaCategory = Category::factory()->create([
            'slug' => 'pizza',
            'target' => slugClass(Product::class),
            'title' => 'Pizza',
            'description' => null,
        ]);
        $pizzaCategory->attachMedia($pizzaMedia);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Margarita',
            'description' => 'The simplest and probably most iconic Italian pizza.'
                . ' Ingredients: dough, mozzarella, tomato paste, basil, oregano.',
            'price' => 125,
            'weight' => 480,
        ]);
        $product->attachMedia($pizzaMedia);
        $product->attachCategories($pizzaCategory);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Romana',
            'description' => 'Ingredients: dough, mozzarella, ham, tomato paste, arugula.',
            'price' => 130,
            'weight' => 420,
        ]);
        $product->attachMedia($pizzaMedia);
        $product->attachCategories($pizzaCategory);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Four Cheese',
            'description' => 'Ingredients: dough, tomato sauce, mozzarella, gorgonzola'
                . ', Parmigiano Reggiano, goat cheese',
            'price' => 160,
            'weight' => 450,
        ]);
        $product->attachMedia($pizzaMedia);
        $product->attachCategories($pizzaCategory);
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
            ->fromFolder('categories')
            ->where('name', 'soup.svg')
            ->first();
        $soupsCategory = Category::factory()->create([
            'slug' => 'soups',
            'target' => slugClass(Product::class),
            'title' => 'Soups',
            'description' => null,
        ]);
        $soupsCategory->attachMedia($soupsMedia);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Tomato Soup',
            'price' => 80,
            'weight' => 300,
        ]);
        $product->attachMedia($soupsMedia);
        $product->attachCategories($soupsCategory);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Celery Soup',
            'price' => 95,
            'weight' => 350,
        ]);
        $product->attachMedia($soupsMedia);
        $product->attachCategories($soupsCategory);
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
            ->fromFolder('categories')
            ->where('name', 'croissant.svg')
            ->first();
        $dessertsCategory = Category::factory()->create([
            'slug' => 'desserts',
            'target' => slugClass(Product::class),
            'title' => 'Desserts',
            'description' => null,
        ]);
        $dessertsCategory->attachMedia($dessertsMedia);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Tiramisu',
            'price' => 75,
            'weight' => 150,
        ]);
        $product->attachMedia($dessertsMedia);
        $product->attachCategories($dessertsCategory);

        $product = Product::factory()->withMenu($kitchen)->create([
            'title' => 'Panna Cotta',
            'price' => 60,
            'weight' => 120,
        ]);
        $product->attachMedia($dessertsMedia);
        $product->attachCategories($dessertsCategory);
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
            ->fromFolder('categories')
            ->where('name', 'alcoholic.svg')
            ->first();
        $alcoholicCategory = Category::factory()->create([
            'slug' => 'alcoholic',
            'target' => slugClass(Product::class),
            'title' => 'Alcoholic',
            'description' => null,
        ]);
        $alcoholicCategory->attachMedia($alcoholicMedia);

        $product = Product::factory()->withMenu($bar)->create([
            'title' => 'Martini',
            'price' => 85,
            'weight' => 120,
        ]);
        $product->attachMedia($alcoholicMedia);
        $product->attachCategories($alcoholicCategory);

        $product = Product::factory()->withMenu($bar)->create([
            'title' => 'Pear Mimosa',
            'description' => 'Champagne and pear nectar combine in a delicate drink.',
            'price' => 72,
            'weight' => 170,
        ]);
        $product->attachMedia($alcoholicMedia);
        $product->attachCategories($alcoholicCategory);

        /** @var Media $nonalcoholicMedia */
        $nonalcoholicMedia = Media::query()
            ->fromFolder('categories')
            ->where('name', 'non-alcoholic.svg')
            ->first();
        $nonalcoholicCategory = Category::factory()->create([
            'slug' => 'non-alcoholic',
            'target' => slugClass(Product::class),
            'title' => 'Non-alcoholic',
            'description' => null,
        ]);
        $nonalcoholicCategory->attachMedia($nonalcoholicMedia);

        $product = Product::factory()->withMenu($bar)->create([
            'title' => 'Mojito',
            'description' => 'Iced Sprite with mint, lime and lemon.',
            'price' => 45,
            'weight' => 250,
        ]);
        $product->attachMedia($nonalcoholicMedia);
        $product->attachCategories($nonalcoholicCategory);

        $product = Product::factory()->withMenu($bar)->create([
            'title' => 'Iced Tea With Plums and Thyme',
            'description' => 'Served nonalcoholic fruit-and-herb blend sipper.',
            'price' => 30,
            'weight' => 200,
        ]);
        $product->attachMedia($nonalcoholicMedia);
        $product->attachCategories($nonalcoholicCategory);
    }
}
