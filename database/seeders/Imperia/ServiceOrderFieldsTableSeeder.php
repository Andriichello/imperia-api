<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ServiceOrderFieldsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('service_order_fields')->delete();

        \DB::table('service_order_fields')->insert(array (
            0 =>
            array (
                'order_id' => 1,
                'service_id' => 3,
                'amount' => 2,
                'duration' => 0,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'order_id' => 2,
                'service_id' => 1,
                'amount' => 1,
                'duration' => 150,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
