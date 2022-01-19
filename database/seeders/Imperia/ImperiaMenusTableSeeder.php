<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ImperiaMenusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('imperia_menus')->delete();

        \DB::table('imperia_menus')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Main',
                'description' => NULL,
                'period_id' => NULL,
                'category_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Summer',
                'description' => NULL,
                'period_id' => 5,
                'category_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Main',
                'description' => NULL,
                'period_id' => NULL,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Summer',
                'description' => NULL,
                'period_id' => 6,
                'category_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
