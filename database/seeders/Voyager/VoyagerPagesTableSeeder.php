<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerPagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('pages')->delete();
    }
}
