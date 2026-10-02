<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Class ArchivedScope.
 */
class ArchivedScope implements Scope
{
    /**
     * Model's column, which is used for archiving.
     *
     * @var string
     */
    protected string $key;

    /**
     * Default archived state.
     *
     * @var string
     */
    protected string $default;

    /**
     * ArchivedScope constructor.
     *
     * @param string $default
     * @param string $key
     */
    public function __construct(string $default = 'without', string $key = 'archived')
    {
        $this->default = $default;
        $this->key = $key;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply(Builder $builder, Model $model): void
    {
        $archived = $this->default;

        // Only staff can choose to see archived records, guests always get the default
        if (request()->has('archived') && $this->isStaff(request()->user())) {
            $archived = request('archived');
        }

        if ($archived === 'only') {
            $builder->where($model->getTable() . '.' . $this->key, true);
        }
        if ($archived === 'without') {
            $builder->where($model->getTable() . '.' . $this->key, false);
        }
    }

    /**
     * Determine if the given user is staff.
     *
     * @param mixed $user
     *
     * @return bool
     */
    protected function isStaff(mixed $user): bool
    {
        return $user instanceof User && $user->isStaff();
    }

    /**
     * @OA\Schema(
     *   schema="ArchivedParameter",
     *   description="Query parameter, which determines if archived records should be
    fetched from database. Available relations: `only`, `with` and `without`, which is a default one.",
     *   enum={"only", "with", "without"},
     *   type="string", example="without"
     * )
     */
}
