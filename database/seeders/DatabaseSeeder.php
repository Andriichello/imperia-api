<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class DatabaseSeeder.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesSeeder::class);
        if (!App::environment('testing')) {
            $this->call(MediaSeeder::class);
            $this->call(DummySeeder::class);
        }
    }
}
