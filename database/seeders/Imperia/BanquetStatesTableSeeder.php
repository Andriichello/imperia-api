<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class BanquetStatesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('banquet_states')->delete();

        \DB::table('banquet_states')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'planning',
                'description' => 'Advance is not paid.',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'booked',
                'description' => 'Advance was already paid.',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'finished',
                'description' => 'Fully processed and paid.',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'cancelled',
                'description' => NULL,
            ),
        ));


    }
}
