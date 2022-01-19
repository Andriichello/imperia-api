<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class TicketsChangeLogTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('tickets_change_log')->delete();

        \DB::table('tickets_change_log')->insert(array (
            0 =>
            array (
                'ticket_id' => 1,
                'price' => '80.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'ticket_id' => 2,
                'price' => '100.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'ticket_id' => 3,
                'price' => '120.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'ticket_id' => 4,
                'price' => '0.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'ticket_id' => 5,
                'price' => '130.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'ticket_id' => 6,
                'price' => '150.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'ticket_id' => 7,
                'price' => '180.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'ticket_id' => 8,
                'price' => '0.00',
                'created_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
