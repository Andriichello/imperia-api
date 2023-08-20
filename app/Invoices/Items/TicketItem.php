<?php

namespace App\Invoices\Items;

use App\Invoices\InvoiceItem;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\TicketOrderField;

/**
 * Class TicketItem.
 *
 * @package App\Invoices\Items
 */
class TicketItem extends InvoiceItem
{
    /**
     * Ordered ticket field.
     *
     * @var TicketOrderField
     */
    protected TicketOrderField $field;

    public static function make(TicketOrderField $field): static
    {
        $item = new static();
        $item->field = $field;

        $item->title($field->ticket->title);
        $item->pricePerUnit($field->ticket->price);
        $item->quantity($field->amount);
        $item->subTotalPrice($field->total);

        return $item;
    }
}
