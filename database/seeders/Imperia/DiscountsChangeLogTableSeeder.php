<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class DiscountsChangeLogTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('discounts_change_log')->delete();

        \DB::table('discounts_change_log')->insert(array (
            0 =>
            array (
                'discount_id' => 1,
                'amount' => NULL,
                'percent' => '10.000',
                'created_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'discount_id' => 2,
                'amount' => NULL,
                'percent' => '20.000',
                'created_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'discount_id' => 3,
                'amount' => NULL,
                'percent' => '33.000',
                'created_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'discount_id' => 4,
                'amount' => 100,
                'percent' => NULL,
                'created_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'discount_id' => 5,
                'amount' => 200,
                'percent' => NULL,
                'created_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'discount_id' => 6,
                'amount' => 300,
                'percent' => NULL,
                'created_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
