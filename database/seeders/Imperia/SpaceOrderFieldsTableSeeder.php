<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class SpaceOrderFieldsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('space_order_fields')->delete();

        \DB::table('space_order_fields')->insert(array (
            0 =>
            array (
                'order_id' => 1,
                'space_id' => 1,
                'beg_datetime' => '2021-10-31 18:00:00',
                'end_datetime' => '2022-10-31 23:00:00',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            1 =>
            array (
                'order_id' => 1,
                'space_id' => 2,
                'beg_datetime' => '2021-10-31 18:00:00',
                'end_datetime' => '2022-10-31 23:00:00',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            2 =>
            array (
                'order_id' => 1,
                'space_id' => 3,
                'beg_datetime' => '2021-10-31 18:00:00',
                'end_datetime' => '2022-10-31 23:00:00',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            3 =>
            array (
                'order_id' => 2,
                'space_id' => 6,
                'beg_datetime' => '2021-04-16 17:00:00',
                'end_datetime' => '2022-04-16 21:00:00',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            4 =>
            array (
                'order_id' => 2,
                'space_id' => 7,
                'beg_datetime' => '2021-04-16 17:00:00',
                'end_datetime' => '2022-04-16 21:00:00',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            5 =>
            array (
                'order_id' => 2,
                'space_id' => 12,
                'beg_datetime' => '2021-04-16 17:00:00',
                'end_datetime' => '2022-04-16 21:00:00',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
        ));


    }
}
