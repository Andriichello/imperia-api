<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\User;
use App\Repositories\Traits\CommentableRepositoryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CustomerRepository.
 */
class CustomerRepository extends CrudRepository
{
    use CommentableRepositoryTrait;

    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Customer::class;

    public function create(array $attributes): Customer
    {
        /** @var Customer $model */
        $model = parent::create($attributes);
        $this->createComments($model, $attributes);
        return $model;
    }

    /**
     * Create customer from existing user.
     *
     * @param User $user
     * @param string|null $phone
     *
     * @return Customer
     */
    public function createFromUser(User $user, ?string $phone = null): Customer
    {
        [$name, $surname] = splitName($user->name);

        $customer = $this->create([
            'name' => $name,
            'surname' => $surname,
            'email' => $user->email,
            'phone' => $phone,
        ]);

        $customer->user_id = $user->id;
        $customer->save();

        return $customer;
    }

    public function update(Model $model, array $attributes): bool
    {
        $result = parent::update($model, $attributes);
        /** @var Customer $model */
        $this->updateComments($model, $attributes);
        return $result;
    }
}
