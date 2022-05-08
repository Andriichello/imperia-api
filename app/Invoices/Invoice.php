<?php

namespace App\Invoices;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use LaravelDaily\Invoices\Invoice as BaseInvoice;

/**
 * Class Invoice.
 *
 * @package App\Invoices
 */
class Invoice extends BaseInvoice
{
    /**
     * @var Collection
     */
    public Collection $comments;

    /**
     * @param Collection $comments
     *
     * @return $this
     */
    public function comments(Collection $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * @param InvoiceItem $item
     *
     * @return string
     */
    public function itemFormattedPrice(InvoiceItem $item): string
    {
        $formatted = $this->formatCurrency($item->price_per_unit);

        if ($item->oncePaidPrice) {
            $hourly = $this->formatCurrency($item->oncePaidPrice);
            return "$formatted + $hourly * hours";
        }

        return $formatted;
    }

    /**
     * @param string $name
     *
     * @return Invoice
     * @throws BindingResolutionException
     */
    public static function make($name = ''): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = parent::make($name);

        return $invoice;
    }
}
