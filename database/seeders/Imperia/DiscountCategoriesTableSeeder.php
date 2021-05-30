<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class DiscountCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('discount_categories')->delete();

        \DB::table('discount_categories')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Percentage',
                'description' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Amount',
                'description' => NULL,
            ),
        ));


    }
}
