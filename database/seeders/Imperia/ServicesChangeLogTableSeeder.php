<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ServicesChangeLogTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('services_change_log')->delete();

        \DB::table('services_change_log')->insert(array (
            0 =>
            array (
                'service_id' => 1,
                'once_paid_price' => '200.00',
                'hourly_paid_price' => '200.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'service_id' => 2,
                'once_paid_price' => '500.00',
                'hourly_paid_price' => '400.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'service_id' => 3,
                'once_paid_price' => '500.00',
                'hourly_paid_price' => '400.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'service_id' => 4,
                'once_paid_price' => '1000.00',
                'hourly_paid_price' => '0.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
