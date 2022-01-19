<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('comments')->delete();

        \DB::table('comments')->insert(array (
            0 =>
            array (
                'id' => 1,
                'text' => 'serve at 18:00',
                'target_id' => 1,
                'target_type' => 'products',
                'container_id' => 2,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 1,
                'text' => 'serve at 17:00',
                'target_id' => 8,
                'target_type' => 'products',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 2,
                'text' => 'serve at 19:00',
                'target_id' => 12,
                'target_type' => 'products',
                'container_id' => 2,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            3 =>
            array (
                'id' => 2,
                'text' => 'serve at 18:00',
                'target_id' => 12,
                'target_type' => 'products',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            4 =>
            array (
                'id' => 3,
                'text' => 'serve at 20:00',
                'target_id' => 13,
                'target_type' => 'products',
                'container_id' => 2,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            5 =>
            array (
                'id' => 3,
                'text' => 'with additional cheese',
                'target_id' => 12,
                'target_type' => 'products',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            6 =>
            array (
                'id' => 4,
                'text' => 'with additional parsley',
                'target_id' => 13,
                'target_type' => 'products',
                'container_id' => 2,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            7 =>
            array (
                'id' => 4,
                'text' => 'serve at 17:00',
                'target_id' => 10,
                'target_type' => 'products',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            8 =>
            array (
                'id' => 5,
                'text' => 'serve at 20:00',
                'target_id' => 3,
                'target_type' => 'services',
                'container_id' => 2,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            9 =>
            array (
                'id' => 5,
                'text' => 'with additional caramel',
                'target_id' => 10,
                'target_type' => 'products',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            10 =>
            array (
                'id' => 6,
                'text' => 'replace melon with kiwi',
                'target_id' => 3,
                'target_type' => 'services',
                'container_id' => 2,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            11 =>
            array (
                'id' => 6,
                'text' => 'start at 17:30',
                'target_id' => 1,
                'target_type' => 'services',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            12 =>
            array (
                'id' => 7,
                'text' => 'make sure clowns are not scary of children',
                'target_id' => 1,
                'target_type' => 'services',
                'container_id' => 3,
                'container_type' => 'banquets',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
