<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class TicketCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('ticket_categories')->delete();

        \DB::table('ticket_categories')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Weekday',
                'description' => 'Tickets, which are available from Monday until Friday',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Weekend',
                'description' => 'Tickets, which are available Saturday until Sunday',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Holiday',
                'description' => 'Tickets, which are available on holidays',
            ),
        ));


    }
}
