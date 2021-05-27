<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class VoyagerTablesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        // \App\Models\User::factory(10)->create();
        $this->call(VoyagerCategoriesTableSeeder::class);
        $this->call(VoyagerDataRowsTableSeeder::class);
        $this->call(VoyagerDataTypesTableSeeder::class);
        $this->call(VoyagerFailedJobsTableSeeder::class);
        $this->call(VoyagerMenuItemsTableSeeder::class);
        $this->call(VoyagerMenusTableSeeder::class);
        $this->call(VoyagerMigrationsTableSeeder::class);
        $this->call(VoyagerPagesTableSeeder::class);
        $this->call(VoyagerPasswordResetsTableSeeder::class);
        $this->call(VoyagerPermissionRoleTableSeeder::class);
        $this->call(VoyagerPermissionsTableSeeder::class);
        $this->call(VoyagerPostsTableSeeder::class);
        $this->call(VoyagerRolesTableSeeder::class);
        $this->call(VoyagerSettingsTableSeeder::class);
        $this->call(VoyagerTranslationsTableSeeder::class);
        $this->call(VoyagerUserRolesTableSeeder::class);
        $this->call(VoyagerUsersTableSeeder::class);
        Schema::enableForeignKeyConstraints();
    }
}
