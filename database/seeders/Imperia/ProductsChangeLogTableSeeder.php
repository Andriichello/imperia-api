<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ProductsChangeLogTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('products_change_log')->delete();

        \DB::table('products_change_log')->insert(array (
            0 =>
            array (
                'product_id' => 1,
                'price' => '120.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'product_id' => 2,
                'price' => '100.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'product_id' => 3,
                'price' => '120.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'product_id' => 4,
                'price' => '180.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'product_id' => 5,
                'price' => '120.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'product_id' => 6,
                'price' => '100.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'product_id' => 7,
                'price' => '100.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'product_id' => 8,
                'price' => '85.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            8 =>
            array (
                'product_id' => 9,
                'price' => '63.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            9 =>
            array (
                'product_id' => 10,
                'price' => '112.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            10 =>
            array (
                'product_id' => 11,
                'price' => '90.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            11 =>
            array (
                'product_id' => 12,
                'price' => '130.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            12 =>
            array (
                'product_id' => 13,
                'price' => '130.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            13 =>
            array (
                'product_id' => 14,
                'price' => '150.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            14 =>
            array (
                'product_id' => 15,
                'price' => '180.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            15 =>
            array (
                'product_id' => 16,
                'price' => '83.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            16 =>
            array (
                'product_id' => 17,
                'price' => '120.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            17 =>
            array (
                'product_id' => 18,
                'price' => '140.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
