<?php

namespace App\Repositories;

use App\Models\Customer;
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

    public function update(Model $model, array $attributes): bool
    {
        $result = parent::update($model, $attributes);
        /** @var Customer $model */
        $this->updateComments($model, $attributes);
        return $result;
    }
}
