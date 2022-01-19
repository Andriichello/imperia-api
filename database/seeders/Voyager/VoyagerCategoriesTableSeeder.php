<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('categories')->delete();
    }
}
