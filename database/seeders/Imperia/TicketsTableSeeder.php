<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class TicketsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('tickets')->delete();

        \DB::table('tickets')->insert(array (
            0 =>
            array (
                'id' => 1,
            'name' => 'Child ticket (weekday)',
                'description' => '0-10 years',
                'price' => '80.00',
                'category_id' => 1,
                'period_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
            'name' => 'Teenager ticket (weekday)',
                'description' => '11-16 years',
                'price' => '100.00',
                'category_id' => 1,
                'period_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
            'name' => 'Adult ticket (weekday)',
                'description' => '16+ years',
                'price' => '120.00',
                'category_id' => 1,
                'period_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
            'name' => 'Birthday ticket (weekday)',
                'description' => 'Available only on birthdays',
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 5,
            'name' => 'Child ticket (weekend)',
                'description' => '0-10 years',
                'price' => '130.00',
                'category_id' => 2,
                'period_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'id' => 6,
            'name' => 'Teenager ticket (weekend)',
                'description' => '11-16 years',
                'price' => '150.00',
                'category_id' => 2,
                'period_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'id' => 7,
            'name' => 'Adult ticket (weekend)',
                'description' => '16+ years',
                'price' => '180.00',
                'category_id' => 2,
                'period_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'id' => 8,
            'name' => 'Birthday ticket (weekend)',
                'description' => 'Available only on birthdays',
                'price' => '0.00',
                'category_id' => 2,
                'period_id' => 2,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
