<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Events\User\Registered;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

    public function create(array $attributes): User
    {
        /** @var User $user */
        $user = parent::create($attributes);
        $user->setRememberToken(Str::random(64));
        $user->save();

        return $user;
    }

    /**
     * Register user with given attributes and role.
     *
     * @param array $attributes
     * @param string $role
     *
     * @return User
     */
    public function register(array $attributes, string $role = UserRole::Customer): User
    {
        if (Arr::has($attributes, ['name', 'surname'])) {
            $attributes['name'] = $attributes['name'] . ' ' . $attributes['surname'];
        }

        $user = $this->create($attributes);
        $user->assignRole($role);

        event(new Registered($user, data_get($attributes, 'phone')));

        return $user;
    }
}
