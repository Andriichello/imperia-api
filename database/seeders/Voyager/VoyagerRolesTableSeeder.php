<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->delete();

        \DB::table('roles')->insert(array (
            0 =>
            array (
                'id' => 3,
                'name' => 'admin',
                'display_name' => 'Administrator',
                'created_at' => '2021-05-30 05:44:13',
                'updated_at' => '2021-05-30 05:44:13',
            ),
            1 =>
            array (
                'id' => 4,
                'name' => 'manager',
                'display_name' => 'Manager',
                'created_at' => '2021-06-06 18:31:04',
                'updated_at' => '2021-06-06 18:31:04',
            ),
            2 =>
            array (
                'id' => 6,
                'name' => 'owner',
                'display_name' => 'Owner',
                'created_at' => '2021-06-06 18:32:46',
                'updated_at' => '2021-06-06 18:32:46',
            ),
            3 =>
            array (
                'id' => 8,
                'name' => 'editor',
                'display_name' => 'Editor',
                'created_at' => '2021-06-06 18:39:41',
                'updated_at' => '2021-06-06 18:39:41',
            ),
        ));
    }
}
