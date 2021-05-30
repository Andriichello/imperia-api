<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class SpaceCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('space_categories')->delete();

        \DB::table('space_categories')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'table',
                'description' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'room',
                'description' => NULL,
            ),
        ));


    }
}
