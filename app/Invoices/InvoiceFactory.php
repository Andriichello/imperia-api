<?php

namespace App\Invoices;

use App\Models\BaseModel;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Restaurant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\Seller;

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
        return Invoice::make('banquet-' . $order->id, $order)
            ->template('banquet')
            ->buyer(self::buyer($order->banquet->customer))
            ->seller(self::seller($order->banquet->restaurant))
            ->addItems(self::items($order));
    }

    /**
     * Create a seller from given restaurant.
     *
     * @param Restaurant $restaurant
     *
     *
     * @return Seller
     */
    public static function seller(Restaurant $restaurant): Seller
    {
        $seller = new Seller();

        $seller->name = $restaurant->name;
        $seller->address = $restaurant->full_address;
        $seller->phone = $restaurant->phone;
        $seller->custom_fields['email'] = $restaurant->email;

        return $seller;
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

        $relations = ['tickets', 'spaces', 'services', 'products'];
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
