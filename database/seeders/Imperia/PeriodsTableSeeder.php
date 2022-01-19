<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class PeriodsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('periods')->delete();

        \DB::table('periods')->insert(array (
            0 =>
            array (
                'id' => 1,
                'beg_datetime_id' => 1,
                'end_datetime_id' => 2,
                'weekdays' => '1,2,3,4,5',
                'is_templatable' => 1,
            ),
            1 =>
            array (
                'id' => 2,
                'beg_datetime_id' => 3,
                'end_datetime_id' => 4,
                'weekdays' => '6,7',
                'is_templatable' => 1,
            ),
            2 =>
            array (
                'id' => 3,
                'beg_datetime_id' => 5,
                'end_datetime_id' => 6,
                'weekdays' => '1,2,3,4,5,6',
                'is_templatable' => 1,
            ),
            3 =>
            array (
                'id' => 4,
                'beg_datetime_id' => 7,
                'end_datetime_id' => 8,
                'weekdays' => '2,3,4,5,6',
                'is_templatable' => 1,
            ),
            4 =>
            array (
                'id' => 5,
                'beg_datetime_id' => 9,
                'end_datetime_id' => 10,
                'weekdays' => NULL,
                'is_templatable' => 1,
            ),
            5 =>
            array (
                'id' => 6,
                'beg_datetime_id' => 11,
                'end_datetime_id' => 12,
                'weekdays' => NULL,
                'is_templatable' => 1,
            ),
        ));


    }
}
