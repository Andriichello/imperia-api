<?php

namespace App\Invoices;

use App\Models\BaseModel;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Restaurant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem as BasicInvoiceItem;
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
        $name = translate('invoices::invoice.banquet', [], 'banquet')
            . '-' . $order->id;

        return Invoice::make($name, $order)
            ->template('banquet')
            ->buyer(self::buyer($order->banquet->customer))
            ->seller(self::seller($order->banquet->restaurant))
            ->addTicketEntries(self::ticketEntries($order))
            ->addItems(self::items($order))
            ->sortItems();
    }

    /**
     * Make invoice from multiple orders.
     *
     * @param Order ...$orders
     *
     * @return Invoice
     * @throws BindingResolutionException
     */
    public static function fromOrders(Order ...$orders): Invoice
    {
        $name = translate('invoices::invoice.banquets', [], 'banquets')
            . '-' . implode(',', Arr::pluck($orders, 'id'));

        $items = [];
        $ticketEntries = [];

        foreach ($orders as $order) {
            $ticketEntries[] = self::ticketEntries($order);

            if (empty($items)) {
                $items = self::items($order)->all();
                continue;
            }

            $added = [];

            foreach (self::items($order) as $new) {
                if (($new instanceof InvoiceItem) === false) {
                    continue;
                }

                $matching = array_filter(
                    $items,
                    function ($item) use ($new) {
                        return $item instanceof InvoiceItem
                            && $item->canBeMerged($new);
                    }
                );

                if (empty($matching)) {
                    $added[] = $new;
                    continue;
                }

                foreach ($matching as $item) {
                    /** @var InvoiceItem $item */
                    $item->mergeWith($new);
                }
            }

            $items = [...$items, ...$added];
        }

        usort(
            $items,
            function ($one, $two) {
                return strcmp($one->title, $two->title);
            }
        );

        return Invoice::make($name)
            ->seller(new Seller())
            ->buyer(new Buyer([]))
            ->template('banquet')
            ->addTicketEntries(...$ticketEntries)
            ->addItems($items)
            ->sortItems();
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
                'email' => $customer->email,
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

        if ($items->isEmpty()) {
            // adding a placeholder item, so the check generation doesn't break
            $item = new BasicInvoiceItem();

            $item->title('Placeholder item...');
            $item->quantity(1);
            $item->pricePerUnit(0);

            $items->add($item);
        }

        return $items;
    }

    /**
     * Get ticket entries for given order.
     *
     * @param Order $order
     *
     * @return array
     */
    public static function ticketEntries(Order $order): array
    {
        $entry = [
            [
                'type' => 'adult',
                'amount' => $order->banquet->adults_amount,
                'price' => $order->banquet->adult_ticket_price,
            ],
            [
                'type' => 'child',
                'amount' => $order->banquet->children_amount,
                'price' => $order->banquet->child_ticket_price,
            ],
        ];

        foreach ($order->banquet->children_amounts ?? [] as $index => $amount) {
            $entry[] = [
                'type' => 'child',
                'amount' => $amount,
                'price' => (float) data_get($order->banquet->child_ticket_prices, $index),
            ];
        }

        return $entry;
    }
}
