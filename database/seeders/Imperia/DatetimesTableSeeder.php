<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class DatetimesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('datetimes')->delete();

        \DB::table('datetimes')->insert(array (
            0 =>
            array (
                'id' => 1,
                'day' => NULL,
                'month' => 1,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            1 =>
            array (
                'id' => 2,
                'day' => NULL,
                'month' => 12,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            2 =>
            array (
                'id' => 3,
                'day' => NULL,
                'month' => 1,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            3 =>
            array (
                'id' => 4,
                'day' => NULL,
                'month' => 12,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            4 =>
            array (
                'id' => 5,
                'day' => NULL,
                'month' => 1,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            5 =>
            array (
                'id' => 6,
                'day' => NULL,
                'month' => 12,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            6 =>
            array (
                'id' => 7,
                'day' => NULL,
                'month' => 1,
                'year' => NULL,
                'hours' => 18,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            7 =>
            array (
                'id' => 8,
                'day' => NULL,
                'month' => 12,
                'year' => NULL,
                'hours' => 3,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            8 =>
            array (
                'id' => 9,
                'day' => NULL,
                'month' => 6,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            9 =>
            array (
                'id' => 10,
                'day' => NULL,
                'month' => 8,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            10 =>
            array (
                'id' => 11,
                'day' => NULL,
                'month' => 6,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
            11 =>
            array (
                'id' => 12,
                'day' => NULL,
                'month' => 8,
                'year' => NULL,
                'hours' => NULL,
                'minutes' => NULL,
                'is_templatable' => 1,
            ),
        ));


    }
}
