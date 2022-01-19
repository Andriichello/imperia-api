<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ProductOrderFieldsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('product_order_fields')->delete();

        \DB::table('product_order_fields')->insert(array (
            0 =>
            array (
                'order_id' => 1,
                'product_id' => 1,
                'amount' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'order_id' => 1,
                'product_id' => 11,
                'amount' => 10,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'order_id' => 1,
                'product_id' => 12,
                'amount' => 5,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'order_id' => 1,
                'product_id' => 13,
                'amount' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'order_id' => 2,
                'product_id' => 8,
                'amount' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'order_id' => 2,
                'product_id' => 10,
                'amount' => 5,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'order_id' => 2,
                'product_id' => 12,
                'amount' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'order_id' => 2,
                'product_id' => 17,
                'amount' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
