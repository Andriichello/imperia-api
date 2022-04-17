<?php

namespace Database\Seeders\Voyager;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
                'password' => Hash::make('pa$$w0rd'),
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
            ),
            1 =>
            array (
                'id' => 4,
                'role_id' => 4,
                'name' => 'Manager',
                'email' => 'manager@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => Hash::make('pa$$w0rd'),
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
            ),
            2 =>
            array (
                'id' => 5,
                'role_id' => 6,
                'name' => 'Owner',
                'email' => 'owner@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => Hash::make('pa$$w0rd'),
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
            ),
            3 =>
            array (
                'id' => 6,
                'role_id' => 8,
                'name' => 'Editor',
                'email' => 'editor@mail.com',
                'avatar' => 'users/default.png',
                'email_verified_at' => NULL,
                'password' => Hash::make('pa$$w0rd'),
                'remember_token' => NULL,
                'settings' => '{"locale":"uk"}',
            ),
        ));
    }
}
