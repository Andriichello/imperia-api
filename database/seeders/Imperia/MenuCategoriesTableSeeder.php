<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class MenuCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('menu_categories')->delete();

        \DB::table('menu_categories')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Bar',
                'description' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Kitchen',
                'description' => NULL,
            ),
        ));


    }
}
