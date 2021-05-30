<?php

namespace Database\Seeders;

use Database\Seeders\Imperia\BanquetStateChangeLogTableSeeder;
use Database\Seeders\Imperia\ImperiaDatabaseSeeder;
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
        $this->call(ImperiaDatabaseSeeder::class);
    }
}
