<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class DiscountsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('discounts')->delete();

        \DB::table('discounts')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => '10% discount',
                'description' => NULL,
                'amount' => NULL,
                'percent' => '10.000',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => '20% discount',
                'description' => NULL,
                'amount' => NULL,
                'percent' => '20.000',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => '33% discount',
                'description' => NULL,
                'amount' => NULL,
                'percent' => '33.000',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => '100 uah discount',
                'description' => NULL,
                'amount' => '100.00',
                'percent' => NULL,
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => '200 uah discount',
                'description' => NULL,
                'amount' => '200.00',
                'percent' => NULL,
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'id' => 6,
                'name' => '300 uah discount',
                'description' => NULL,
                'amount' => '300.00',
                'percent' => NULL,
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
