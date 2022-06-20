<?php

namespace App\Invoices;

use App\Models\BaseModel;
use App\Models\Customer;
use App\Models\Orders\Order;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use LaravelDaily\Invoices\Classes\Buyer;

/**
 * Class InvoiceFactory.
 *
 * @package App\Invoices
 */
class InvoiceFactory
{
    /**
     * Make invoice from an order.
     *
     * @param Order $order
     *
     * @return Invoice
     * @throws BindingResolutionException
     */
    public static function fromOrder(Order $order): Invoice
    {
        return Invoice::make('receipt-' . $order->id)
            ->template('custom')
            ->buyer(self::buyer($order->banquet->customer))
            ->addItems(self::items($order))
            ->comments($order->comments->pluck('text'));
    }

    /**
     * Create a buyer from given customer.
     *
     * @param Customer $customer
     *
     * @return Buyer
     */
    public static function buyer(Customer $customer): Buyer
    {
        return new Buyer([
            'name' => $customer->fullName,
            'custom_fields' => [
                'phone' => $customer->phone,
                'email' => $customer->email ?? 'unknown',
            ],
        ]);
    }

    /**
     * Create invoice items from given order.
     *
     * @param Order $order
     *
     * @return Collection
     */
    public static function items(Order $order): Collection
    {
        $items = new Collection();

        $relations = ['tickets','spaces', 'services', 'products'];
        foreach ($relations as $relation) {
            /** @var BaseModel[]|Collection $fields */
            $fields = $order->$relation;
            $mapped = $fields->map(
                fn($field) => InvoiceItemFactory::fromField($field)
            );

            $items->push(...$mapped->all());
        }

        return $items;
    }
}
