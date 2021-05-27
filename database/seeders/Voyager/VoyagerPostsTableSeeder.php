<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerPostsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('posts')->delete();



    }
}
