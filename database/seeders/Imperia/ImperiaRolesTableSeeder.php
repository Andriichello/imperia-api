<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ImperiaRolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('imperia_roles')->delete();

        \DB::table('imperia_roles')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'owner',
                'description' => NULL,
                'can_read' => 1,
                'can_insert' => 1,
                'can_modify' => 1,
                'is_owner' => 1,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'admin',
                'description' => NULL,
                'can_read' => 1,
                'can_insert' => 1,
                'can_modify' => 1,
                'is_owner' => 0,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'manager',
                'description' => NULL,
                'can_read' => 1,
                'can_insert' => 1,
                'can_modify' => 0,
                'is_owner' => 0,
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
