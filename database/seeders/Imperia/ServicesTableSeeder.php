<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ServicesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('services')->delete();

        \DB::table('services')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Clown show',
                'description' => 'A fun clown that will bring a lot of joy to your children',
                'once_paid_price' => '200.00',
                'hourly_paid_price' => '200.00',
                'category_id' => 1,
                'period_id' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Barman show',
                'description' => NULL,
                'once_paid_price' => '500.00',
                'hourly_paid_price' => '400.00',
                'category_id' => 1,
                'period_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Carved fruits arrangement',
                'description' => 'Carved apples, bananas, mangoes and melons',
                'once_paid_price' => '500.00',
                'hourly_paid_price' => '400.00',
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Champagne glasses pyramid',
                'description' => NULL,
                'once_paid_price' => '1000.00',
                'hourly_paid_price' => '0.00',
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
