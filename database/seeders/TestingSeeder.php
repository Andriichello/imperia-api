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
    }

    /**
     * Seed tickets.
     *
     * @return void
     */
    public function seedTickets(): void
    {
        Ticket::factory()->count(4)->create();
    }

    /**
     * Seed services.
     *
     * @return void
     */
    public function seedServices(): void
    {
        Service::factory()->count(10)->create();
    }

    /**
     * Seed spaces.
     *
     * @return void
     */
    public function seedSpaces(): void
    {
        Space::factory()->count(10)->create();
    }

    /**
     * Seed products.
     *
     * @return void
     */
    public function seedProducts(): void
    {
        foreach ([4, 8, 12] as $index => $count) {
            $menu = Menu::factory()->create([
                'title' => 'Menu #' . ($index + 1),
            ]);

            Product::factory()->withMenu($menu)
                ->count($count)->create();
        }
    }
}
