<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class BanquetsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('banquets')->delete();

        \DB::table('banquets')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'New Year Eve Party',
                'description' => NULL,
                'advance_amount' => '4700.00',
                'beg_datetime' => '2021-12-31 22:00:00',
                'end_datetime' => '2022-01-01 01:00:00',
                'state_id' => 1,
                'creator_id' => 3,
                'customer_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
                'paid_at' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Halloween Party',
                'description' => NULL,
                'advance_amount' => '2300.00',
                'beg_datetime' => '2021-10-31 18:00:00',
                'end_datetime' => '2022-10-31 23:00:00',
                'state_id' => 2,
                'creator_id' => 3,
                'customer_id' => 6,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
                'paid_at' => NULL,
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Johny\'s Birthday Party',
                'description' => NULL,
                'advance_amount' => '900.00',
                'beg_datetime' => '2021-04-16 17:00:00',
                'end_datetime' => '2022-04-16 21:00:00',
                'state_id' => 2,
                'creator_id' => 1,
                'customer_id' => 9,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
                'paid_at' => NULL,
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Ann\'s Birthday Party',
                'description' => NULL,
                'advance_amount' => '2000.00',
                'beg_datetime' => '2021-03-28 13:00:00',
                'end_datetime' => '2022-03-28 18:00:00',
                'state_id' => 3,
                'creator_id' => 2,
                'customer_id' => 9,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
                'paid_at' => NULL,
            ),
        ));


    }
}
