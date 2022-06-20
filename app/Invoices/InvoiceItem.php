<?php

namespace App\Invoices;

use Illuminate\Support\Collection;
use LaravelDaily\Invoices\Classes\InvoiceItem as BaseItem;

/**
 * Class InvoiceItem.
 *
 * @package App\Invoices
 */
class InvoiceItem extends BaseItem
{
    /**
     * @var Collection
     */
    public Collection $comments;

    /**
     * @var float|null
     */
    public ?float $oncePaidPrice = null;

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
     * @param ?float $price
     *
     * @return $this
     */
    public function oncePaidPrice(?float $price): static
    {
        $this->oncePaidPrice = $price;

        return $this;
    }
}
