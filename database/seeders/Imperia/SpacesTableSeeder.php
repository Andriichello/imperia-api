<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class SpacesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('spaces')->delete();

        \DB::table('spaces')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Table',
                'number' => 1,
                'floor' => 1,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Table',
                'number' => 2,
                'floor' => 1,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Table',
                'number' => 3,
                'floor' => 1,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Table',
                'number' => 4,
                'floor' => 1,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'Table',
                'number' => 5,
                'floor' => 1,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'Table',
                'number' => 6,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'Table',
                'number' => 7,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'Table',
                'number' => 8,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'Table',
                'number' => 9,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 1,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            9 =>
            array (
                'id' => 10,
                'name' => 'Spaceship',
                'number' => 1,
                'floor' => 1,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            10 =>
            array (
                'id' => 11,
                'name' => 'Archery',
                'number' => 2,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            11 =>
            array (
                'id' => 12,
                'name' => 'Lego',
                'number' => 2,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            12 =>
            array (
                'id' => 13,
                'name' => 'Bikini Bottom',
                'number' => 3,
                'floor' => 2,
                'description' => NULL,
                'price' => '0.00',
                'category_id' => 2,
                'period_id' => NULL,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
