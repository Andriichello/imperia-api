<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ImperiaDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        $this->call(BanquetStateChangeLogTableSeeder::class);
        $this->call(BanquetStatesTableSeeder::class);
        $this->call(BanquetsTableSeeder::class);
        $this->call(CommentsTableSeeder::class);
        $this->call(CustomerFamilyMembersTableSeeder::class);
        $this->call(CustomersTableSeeder::class);
        $this->call(DatetimesTableSeeder::class);
        $this->call(DiscountCategoriesTableSeeder::class);
        $this->call(DiscountsTableSeeder::class);
        $this->call(DiscountsChangeLogTableSeeder::class);
        $this->call(ImperiaMenusTableSeeder::class);
        $this->call(ImperiaRolesTableSeeder::class);
        $this->call(ImperiaUsersTableSeeder::class);
        $this->call(MenuCategoriesTableSeeder::class);
        $this->call(MigrationsTableSeeder::class);
        $this->call(PeriodsTableSeeder::class);
        $this->call(ProductCategoriesTableSeeder::class);
        $this->call(ProductOrderFieldsTableSeeder::class);
        $this->call(ProductOrdersTableSeeder::class);
        $this->call(ProductsTableSeeder::class);
        $this->call(ProductsChangeLogTableSeeder::class);
        $this->call(ServiceCategoriesTableSeeder::class);
        $this->call(ServiceOrderFieldsTableSeeder::class);
        $this->call(ServiceOrdersTableSeeder::class);
        $this->call(ServicesTableSeeder::class);
        $this->call(ServicesChangeLogTableSeeder::class);
        $this->call(SpaceCategoriesTableSeeder::class);
        $this->call(SpaceOrderFieldsTableSeeder::class);
        $this->call(SpaceOrdersTableSeeder::class);
        $this->call(SpacesTableSeeder::class);
        $this->call(SpacesChangeLogTableSeeder::class);
        $this->call(TicketCategoriesTableSeeder::class);
        $this->call(TicketOrderFieldsTableSeeder::class);
        $this->call(TicketOrdersTableSeeder::class);
        $this->call(TicketsTableSeeder::class);
        $this->call(TicketsChangeLogTableSeeder::class);

        Schema::enableForeignKeyConstraints();
    }
}
