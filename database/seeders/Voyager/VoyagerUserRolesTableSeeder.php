<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerUserRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('user_roles')->delete();



    }
}
