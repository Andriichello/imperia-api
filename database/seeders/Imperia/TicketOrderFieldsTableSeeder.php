<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class TicketOrderFieldsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('ticket_order_fields')->delete();

        \DB::table('ticket_order_fields')->insert(array (
            0 =>
            array (
                'order_id' => 1,
                'ticket_id' => 1,
                'amount' => 10,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            1 =>
            array (
                'order_id' => 1,
                'ticket_id' => 2,
                'amount' => 3,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            2 =>
            array (
                'order_id' => 1,
                'ticket_id' => 3,
                'amount' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            3 =>
            array (
                'order_id' => 1,
                'ticket_id' => 4,
                'amount' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            4 =>
            array (
                'order_id' => 2,
                'ticket_id' => 5,
                'amount' => 5,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            5 =>
            array (
                'order_id' => 2,
                'ticket_id' => 7,
                'amount' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
            6 =>
            array (
                'order_id' => 2,
                'ticket_id' => 8,
                'amount' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',

            ),
        ));


    }
}
