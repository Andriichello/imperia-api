<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;

class VoyagerUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('users')->delete();

        \DB::table('users')->insert(array (
            0 =>
            array (
                'id' => 3,
                'role_id' => 3,
                'name' => 'Administrator',
                'email' => 'admin@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$3Nl4Q3R/D8Rdr1ls6DVydePSDj5BszU.N2yYkZkF5lBbB1dKuMS6K',
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
                'created_at' => '2021-06-06 18:38:15',
                'updated_at' => '2021-06-06 18:38:44',
            ),
            1 =>
            array (
                'id' => 4,
                'role_id' => 4,
                'name' => 'Manager',
                'email' => 'manager@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$dniFr3ootB/I0yOQ3GBkxeIR.YoQsl4pgf4lv/lNhrtlzWrJLTkpS',
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
                'created_at' => '2021-06-06 18:40:25',
                'updated_at' => '2021-06-06 18:40:25',
            ),
            2 =>
            array (
                'id' => 5,
                'role_id' => 6,
                'name' => 'Owner',
                'email' => 'owner@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$lN3KcoXB7QSXIavLsc/RAuQmo8N74YposJW.ZWe6Mvh98Eip/6RW2',
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
                'created_at' => '2021-06-06 18:40:47',
                'updated_at' => '2021-06-06 18:40:47',
            ),
            3 =>
            array (
                'id' => 6,
                'role_id' => 8,
                'name' => 'Editor',
                'email' => 'editor@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => '$2y$10$LCp5CK4NRzOP2.XS1K35w.5TwNdbnAZxT7TNREqvzR9txuAxpVexO',
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
                'created_at' => '2021-06-06 18:41:10',
                'updated_at' => '2021-06-06 18:41:10',
            ),
        ));


    }
}
