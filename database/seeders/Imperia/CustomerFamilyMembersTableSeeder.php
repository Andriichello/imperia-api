<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class CustomerFamilyMembersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('customer_family_members')->delete();

        \DB::table('customer_family_members')->insert(array (
            0 =>
            array (
                'id' => 1,
                'customer_id' => 1,
                'name' => 'Julia',
                'birthdate' => '2005-03-15',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'customer_id' => 1,
                'name' => 'Jack',
                'birthdate' => '2002-08-02',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'customer_id' => 7,
                'name' => 'Tim',
                'birthdate' => '2010-01-28',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'customer_id' => 3,
                'name' => 'Peter',
                'birthdate' => '2014-12-13',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 5,
                'customer_id' => 3,
                'name' => 'Sam',
                'birthdate' => '2016-06-28',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
