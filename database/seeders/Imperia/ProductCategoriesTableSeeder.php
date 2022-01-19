<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ProductCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('product_categories')->delete();

        \DB::table('product_categories')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Hot drinks',
                'description' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Cold drinks',
                'description' => NULL,
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Soups',
                'description' => NULL,
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Pizza',
                'description' => NULL,
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'Pasta',
                'description' => NULL,
            ),
        ));


    }
}
