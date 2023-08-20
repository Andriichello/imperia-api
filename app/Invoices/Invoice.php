<?php

namespace App\Invoices;

use App\Invoices\Items\ProductItem;
use App\Invoices\Items\ServiceItem;
use App\Invoices\Items\SpaceItem;
use App\Invoices\Items\TicketItem;
use App\Models\Orders\Order;
use Illuminate\Contracts\Container\BindingResolutionException;
use LaravelDaily\Invoices\Invoice as BaseInvoice;

/**
 * Class Invoice.
 *
 * @package App\Invoices
 */
class Invoice extends BaseInvoice
{
    /**
     * Invoice order.
     *
     * @var Order
     */
    protected Order $order;

    /**
     * Create a new instance of invoice.
     *
     * @param string $name
     * @param Order|null $order
     *
     * @return Invoice
     * @throws BindingResolutionException
     */
    public static function make($name = '', ?Order $order = null): Invoice
    {
        $name = strlen($name) || $order === null
            ? $name : "receipt-{$order->id}";

        /** @var Invoice $invoice */
        $invoice = parent::make($name);
        $invoice->order = $order;

        return $invoice;
    }

    /**
     * Get comments.
     *
     * @return array
     */
    public function getComments(): array
    {
        return $this->order->comments?->toArray() ?? [];
    }

    /**
     * Get date.
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->order->banquet->start_at->format('d/m/Y');
    }

    /**
     * Get start time.
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->order->banquet->start_at->format('H:i');
    }

    /**
     * Get end time.
     *
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->order->banquet->end_at->format('H:i');
    }

    /**
     * @param InvoiceItem $item
     *
     * @return string
     */
    public function itemFormattedPrice(InvoiceItem $item): string
    {
        $formatted = $this->formatCurrency($item->price_per_unit);

        if ($item->getOncePaidPrice()) {
            $hourly = $this->formatCurrency($item->getOncePaidPrice());
            return "$formatted + $hourly * hours";
        }

        return $formatted;
    }

    /**
     * @return ProductItem[]
     */
    public function getProducts(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof ProductItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return SpaceItem[]
     */
    public function getSpaces(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof SpaceItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return ServiceItem[]
     */
    public function getServices(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof SpaceItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return TicketItem[]
     */
    public function getTickets(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof TicketItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Get total price of given items.
     *
     * @param array $items
     *
     * @return float
     */
    public function getTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            /** @var InvoiceItem $item */
            $total += $item->getTotal() ?? 0.0;
        }

        return $total;
    }
}
