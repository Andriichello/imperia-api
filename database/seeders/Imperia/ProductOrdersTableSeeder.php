<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ProductOrdersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('product_orders')->delete();

        \DB::table('product_orders')->insert(array (
            0 =>
            array (
                'id' => 1,
                'banquet_id' => 2,
                'discount_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'banquet_id' => 3,
                'discount_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
