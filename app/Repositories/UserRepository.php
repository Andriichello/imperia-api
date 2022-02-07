<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class UserRepository.
 */
class UserRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = User::class;

    public function create(array $attributes, string $role = UserRole::Admin): User
    {
        /** @var User $user */
        $user = parent::create($attributes);
        $user->setRememberToken(Str::random(10));
        $user->save();

        $user->assignRole($role);
        return $user;
    }
}
