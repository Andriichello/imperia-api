<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('customers')->delete();

        \DB::table('customers')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Anthony',
                'surname' => 'Davis',
                'phone' => '+38 050 000 1111',
                'email' => 'anthony.davis@gmail.com',
                'birthdate' => '1993-03-11',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'LeBron',
                'surname' => 'James',
                'phone' => '+38 050 000 2222',
                'email' => 'lebron.james@gmail.com',
                'birthdate' => '1984-12-30',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'James',
                'surname' => 'Harden',
                'phone' => '+38 050 000 3333',
                'email' => NULL,
                'birthdate' => '1989-08-26',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Luka',
                'surname' => 'Doncic',
                'phone' => '+38 050 000 4444',
                'email' => 'luka.doncic@gmail.com',
                'birthdate' => '1999-02-28',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'Lamelo',
                'surname' => 'Ball',
                'phone' => '+38 050 000 5555',
                'email' => NULL,
                'birthdate' => '2001-08-22',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'Dwight',
                'surname' => 'Howard',
                'phone' => '+38 050 000 6666',
                'email' => NULL,
                'birthdate' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'Dwyane',
                'surname' => 'Wade',
                'phone' => '+38 050 000 7777',
                'email' => NULL,
                'birthdate' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'Jimmy',
                'surname' => 'Butler',
                'phone' => '+38 050 000 8888',
                'email' => NULL,
                'birthdate' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'Tim',
                'surname' => 'Duncan',
                'phone' => '+38 050 000 9999',
                'email' => 'tim.duncan@gmail.com',
                'birthdate' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
