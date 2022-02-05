<?php

namespace Database\Seeders;

use App\Enums\FamilyRelation;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\FamilyMember;
use App\Models\Menu;
use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class TestingSeeder.
 */
class TestingSeeder extends Seeder
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
        $johnDoe = Customer::factory()
            ->create([
                'name' => 'John',
                'surname' => 'Doe',
                'email' => 'john.doe@email.com',
                'phone' => '+38 050 777 7777',
                'birthdate' => '1986-01-26',
            ]);
        $johnDoe->attachComments(
            'This is the first test customer.',
            'John Doe is a typical fake name.',
        );
        FamilyMember::factory()
            ->withRelative($johnDoe, FamilyRelation::Child())
            ->create([
                'name' => 'Jenny Doe',
                'birthdate' => '2010-07-03',
            ]);
        FamilyMember::factory()
            ->withRelative($johnDoe, FamilyRelation::Child())
            ->create([
                'name' => 'Tommy Doe',
                'birthdate' => '2013-07-03',
            ]);

        Customer::factory()
            ->create([
                'name' => 'Richard',
                'surname' => 'Jefferson',
                'email' => 'richard.jefferson@email.com',
                'phone' => '+38 050 555 5555',
                'birthdate' => '1973-08-03',
            ])->attachComments('He played in NBA for Cleveland Cavaliers.');
    }

    /**
     * Seed tickets.
     *
     * @return void
     */
    public function seedTickets(): void
    {
        $workday = Category::factory()->create([
            'slug' => 'work-day-tickets',
            'title' => 'Work Day Tickets',
            'description' => 'Tickets that are available from Monday to Thursday.',
        ]);
        Ticket::factory()->create([
            'title' => 'Child workday ticket',
            'description' => 'Tickets that are available for customers younger than 14 years.',
            'price' => 50,
        ])->attachCategories($workday);
        Ticket::factory()->create([
            'title' => 'Adult workday ticket',
            'description' => 'Tickets that are available for customers older than 14 years.',
            'price' => 75,
        ])->attachCategories($workday);

        $weekend = Category::factory()->create([
            'slug' => 'weekend-tickets',
            'title' => 'Weekend Tickets',
            'description' => 'Tickets that are available from Friday to Sunday.',
        ]);
        Ticket::factory()->create([
            'title' => 'Child weekend ticket',
            'description' => 'Tickets that are available for customers younger than 14 years.',
            'price' => 80,
        ])->attachCategories($weekend);
        Ticket::factory()->create([
            'title' => 'Adult weekend ticket',
            'description' => 'Tickets that are available for customers older than 14 years.',
            'price' => 100,
        ])->attachCategories($weekend);
    }

    /**
     * Seed services.
     *
     * @return void
     */
    public function seedServices(): void
    {
        $indoors = Category::factory()->create([
            'slug' => 'indoors',
            'title' => 'Indoors',
            'description' => null,
        ]);
        Service::factory()->create([
            'title' => 'Clown Show',
            'once_paid_price' => 300,
            'hourly_paid_price' => 200,
        ])->attachCategories($indoors);
        Service::factory()->create([
            'title' => 'Fruits Carving',
            'once_paid_price' => 1000,
        ])->attachCategories($indoors);

        $outdoors = Category::factory()->create([
            'slug' => 'outdoors',
            'title' => 'Outdoors',
            'description' => null,
        ]);
        Service::factory()->create([
            'title' => 'Fire Show',
            'once_paid_price' => 1200,
            'hourly_paid_price' => 600,
        ])->attachCategories($outdoors);
        Service::factory()->create([
            'title' => 'Magic Show',
            'once_paid_price' => 1000,
            'hourly_paid_price' => 500,
        ])->attachCategories($outdoors);
    }

    /**
     * Seed spaces.
     *
     * @return void
     */
    public function seedSpaces(): void
    {
        $rooms = Category::factory()->create([
            'slug' => 'rooms',
            'title' => 'Rooms',
            'description' => null,
        ]);
        $tables = Category::factory()->create([
            'slug' => 'tables',
            'title' => 'Tables',
            'description' => null,
        ]);

        for ($i = 1; $i <= 2; $i++) {
            for ($j = 1; $j <= 5; $j++) {
                Space::factory()->create([
                    'title' => "Table #$j($i)",
                    'floor' => $i,
                    'number' => $j,
                    'price' => 0.0,
                ])->attachCategories($tables);

                if ($j <= 3) {
                    Space::factory()->create([
                        'title' => "Room #$j($i)",
                        'floor' => $i,
                        'number' => $j,
                        'price' => rand(1, 10) * 10,
                    ])->attachCategories($rooms);
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
        $pizza = Category::factory()->create([
            'slug' => 'pizza',
            'title' => 'Pizza',
            'description' => null,
        ]);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Margarita',
            'description' => 'The simplest and probably most iconic Italian pizza.'
                . ' Ingredients: dough, mozzarella, tomato paste, basil, oregano.',
            'price' => 125,
            'weight' => 480,
        ])->attachCategories($pizza);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Romana',
            'description' => 'Ingredients: dough, mozzarella, ham, tomato paste, arugula.',
            'price' => 130,
            'weight' => 420,
        ])->attachCategories($pizza);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Four Cheese',
            'description' => 'Ingredients: dough, tomato sauce, mozzarella, gorgonzola'
                . ', Parmigiano Reggiano, goat cheese',
            'price' => 160,
            'weight' => 450,
        ])->attachCategories($pizza);
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
        $soups = Category::factory()->create([
            'slug' => 'soups',
            'title' => 'Soups',
            'description' => null,
        ]);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Tomato Soup',
            'price' => 80,
            'weight' => 300,
        ])->attachCategories($soups);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Celery Soup',
            'price' => 95,
            'weight' => 350,
        ])->attachCategories($soups);
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
        $desserts = Category::factory()->create([
            'slug' => 'desserts',
            'title' => 'Desserts',
            'description' => null,
        ]);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Tiramisu',
            'price' => 75,
            'weight' => 150,
        ])->attachCategories($desserts);
        Product::factory()->withMenu($kitchen)->create([
            'title' => 'Panna Cotta',
            'price' => 60,
            'weight' => 120,
        ])->attachCategories($desserts);
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
        $alcoholic = Category::factory()->create([
            'slug' => 'alcoholic',
            'title' => 'Alcoholic',
            'description' => null,
        ]);
        Product::factory()->withMenu($bar)->create([
            'title' => 'Martini',
            'price' => 85,
            'weight' => 120,
        ])->attachCategories($alcoholic);
        Product::factory()->withMenu($bar)->create([
            'title' => 'Pear Mimosa',
            'description' => 'Champagne and pear nectar combine in a delicate drink.',
            'price' => 72,
            'weight' => 170,
        ])->attachCategories($alcoholic);

        $nonalcoholic = Category::factory()->create([
            'slug' => 'non-alcoholic',
            'title' => 'Non-alcoholic',
            'description' => null,
        ]);
        Product::factory()->withMenu($bar)->create([
            'title' => 'Mojito',
            'description' => 'Iced Sprite with mint, lime and lemon.',
            'price' => 45,
            'weight' => 250,
        ])->attachCategories($nonalcoholic);
        Product::factory()->withMenu($bar)->create([
            'title' => 'Iced Tea With Plums and Thyme',
            'description' => 'Served nonalcoholic fruit-and-herb blend sipper.',
            'price' => 30,
            'weight' => 200,
        ])->attachCategories($nonalcoholic);
    }
}
