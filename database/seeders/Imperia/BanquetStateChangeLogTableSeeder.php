<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class BanquetStateChangeLogTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('banquet_state_change_log')->delete();

        \DB::table('banquet_state_change_log')->insert(array (
            0 =>
            array (
                'state_id' => 1,
                'banquet_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'state_id' => 2,
                'banquet_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'state_id' => 2,
                'banquet_id' => 3,
                'created_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'state_id' => 3,
                'banquet_id' => 4,
                'created_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
