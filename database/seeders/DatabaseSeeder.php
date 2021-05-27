<?php

namespace Database\Seeders;

use Database\Seeders\Voyager\VoyagerTablesSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(VoyagerTablesSeeder::class);
    }
}
