<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerMenusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('menus')->delete();

        \DB::table('menus')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'admin',
                'created_at' => '2021-05-27 11:51:32',
                'updated_at' => '2021-05-27 11:51:32',
            ),
        ));
    }
}
