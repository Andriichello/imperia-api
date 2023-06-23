<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Class UsersSeeder.
 */
class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()
            ->withRole(UserRole::Admin())
            ->create([
                'name' => 'Super',
                'email' => 'super@email.com',
                'password' => 'pa$$w0rd',
                'remember_token' => 'super',
                'metadata' => json_encode([
                    'isPreviewOnly' => false,
                ])
            ]);
    }
}
