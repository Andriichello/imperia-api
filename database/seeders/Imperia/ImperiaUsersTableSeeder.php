<?php

namespace Database\Seeders\Imperia;

use Illuminate\Database\Seeder;

class ImperiaUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('imperia_users')->delete();

        \DB::table('imperia_users')->insert(array (
            0 =>
            array (
                'id' => 1,
                'role_id' => 1,
                'name' => 'Owner',
                'password' => 'OWNER_USER',
                'api_token' => 'eb05722d5d6fe8e4ebb0e43fba89b9a160d534541d6fc122bf194b0dcc17fb27',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            1 =>
            array (
                'id' => 2,
                'role_id' => 2,
                'name' => 'Admin',
                'password' => 'ADMIN_USER',
                'api_token' => 'b9461ee404534f09dfe0f1697cfa796fb2df67e9dc1e7457d177451d1abb9262',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
            2 =>
            array (
                'id' => 3,
                'role_id' => 3,
                'name' => 'Manager',
                'password' => 'MANAGER_USER',
                'api_token' => '7f8053ef001d3d626e92da75f8e1f47544f526ebd450cc06416f2c1980e3969f',
                'created_at' => '2021-05-27 15:15:42',
                'updated_at' => '2021-05-27 15:15:42',
            ),
        ));


    }
}
