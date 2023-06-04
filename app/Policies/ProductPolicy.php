<?php

namespace App\Policies;

use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\User;
use App\Policies\Base\CrudPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

/**
 * Class ProductPolicy.
 */
class ProductPolicy extends CrudPolicy
{
    /**
     * Get the model of the policy.
     *
     * @return Model|string
     */
    public function model(): Model|string
    {
        return Product::class;
    }

    /**
     * Perform pre-authorization checks.
     *
     * @param User|null $user
     * @param string $ability
     *
     * @return Response|bool|null
     */
    public function before(?User $user, string $ability): Response|bool|null
    {
        if (in_array($ability, ['viewAny', 'view'])) {
            return true;
        }

        return $user->isAdmin();
    }

    /**
     * Determine if user can attach categories to model.
     *
     * @param User $user
     * @param Product $product
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function attachAnyCategory(User $user, Product $product): Response|bool|null
    {
        return $user->isAdmin();
    }

    /**
     * Determine if user can detach category from model.
     *
     * @param User $user
     * @param Product $product
     * @param Category $category
     *
     * @return Response|bool|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function detachCategory(User $user, Product $product, Category $category): Response|bool|null
    {
        return $user->isAdmin();
    }
}
