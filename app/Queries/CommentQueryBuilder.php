<?php

namespace App\Queries;

use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\ServiceOrderField;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrderField;
use App\Models\User;
use App\Nova\FamilyMember;
use App\Providers\MorphServiceProvider;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommentQueryBuilder.
 */
class CommentQueryBuilder extends BaseQueryBuilder
{
    /**
     * Apply index query conditions.
     *
     * @param User|null $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(?User $user = null): static
    {
        if ($user->isStaff()) {
            return $this;
        }

        return $this->asForCustomer($user);
    }

    /**
     * Limit comments to only those that given customer can see.
     *
     * @param User $user
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asForCustomer(User $user): static
    {
        $this->notForClasses(User::class, Customer::class, FamilyMember::class);

//        $banquets = $user->banquets()
//            ->pluck('id')
//            ->all();
//
//        $this->whereWrapped(function (CommentQueryBuilder $query) use ($banquets) {
//            $query->fromBanquets(...$banquets);
//        });

        return $this;
    }

    /**
     * Include only comments from given banquets.
     *
     * @param Banquet|int ...$banquets
     *
     * @return static
     */
    public function fromBanquets(Banquet|int ...$banquets): static
    {
        $ids = $this->extract('id', ...$banquets);

        $this->whereWrapped(function (CommentQueryBuilder $query) use ($ids) {
            $query->forClasses(Banquet::class)
                ->whereCommentableId($ids);
        });

        if (!empty($ids)) {
            $orders = Order::query()
                ->whereIn('banquet_id', $ids)
                ->pluck('id')
                ->all();

            $this->orFromOrders(...$orders);
        }

        return $this;
    }

    /**
     * @param Banquet|int ...$banquets
     *
     * @return static
     */
    public function orFromBanquets(Banquet|int ...$banquets): static
    {
        $this->orWhereWrapped(function (CommentQueryBuilder $query) use ($banquets) {
            $query->fromBanquets(...$banquets);
        });

        return $this;
    }

    /**
     * Include only comments from given orders.
     *
     * @param Order|int ...$orders
     *
     * @return static
     */
    public function fromOrders(Order|int ...$orders): static
    {
        $ids = $this->extract('id', ...$orders);

        $this->whereWrapped(function (CommentQueryBuilder $query) use ($ids) {
            $query->forClasses(Order::class)
                ->whereCommentableId($ids);
        });

        if (!empty($ids)) {
            $products = ProductOrderField::query()
                ->whereIn('order_id', $ids)
                ->pluck('id')
                ->all();

            $this->orFromOrderFields(ProductOrderField::class, ...$products);

            $tickets = TicketOrderField::query()
                ->whereIn('order_id', $ids)
                ->pluck('id')
                ->all();

            $this->orFromOrderFields(TicketOrderField::class, ...$tickets);

            $spaces = SpaceOrderField::query()
                ->whereIn('order_id', $ids)
                ->pluck('id')
                ->all();

            $this->orFromOrderFields(SpaceOrderField::class, ...$spaces);

            $services = ServiceOrderField::query()
                ->whereIn('order_id', $ids)
                ->pluck('id')
                ->all();

            $this->orFromOrderFields(ServiceOrderField::class, ...$services);
        }

        return $this;
    }

    /**
     * @param Order|int ...$orders
     *
     * @return static
     */
    public function orFromOrders(Order|int ...$orders): static
    {
        $this->orWhereWrapped(function (CommentQueryBuilder $query) use ($orders) {
            $query->fromOrders(...$orders);
        });

        return $this;
    }

    /**
     * Include only comments for given order fields.
     *
     * @param string $class
     * @param Model|int ...$fields
     *
     * @return static
     */
    public function fromOrderFields(string $class, Model|int ...$fields): static
    {
        $ids = $this->extract('id', ...$fields);

        $this->whereWrapped(function (CommentQueryBuilder $query) use ($class, $ids) {
            $query->forClasses($class)->whereCommentableId($ids);
        });

        return $this;
    }

    /**
     * @param string $class
     * @param Model|int ...$fields
     *
     * @return static
     */
    public function orFromOrderFields(string $class, Model|int ...$fields): static
    {
        $this->orWhereWrapped(function (CommentQueryBuilder $query) use ($class, $fields) {
            $query->fromOrderFields($class, ...$fields);
        });

        return $this;
    }

    /**
     * Exclude comments for given commentable classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function notForClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereNotIn('commentable_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only comments for given commentable classes.
     *
     * @param string ...$classes
     *
     * @return static
     */
    public function forClasses(string ...$classes): static
    {
        $morphs = MorphServiceProvider::getMorphMap($classes);

        $this->whereIn('commentable_type', array_keys($morphs));

        return $this;
    }

    /**
     * Include only comments with given commentable ids.
     *
     * @param array $values
     *
     * @return static
     */
    public function whereCommentableId(array $values): static
    {
        $this->whereIn('commentable_id', $values);

        return $this;
    }
}
