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

    /**
     * Make a new instance of TicketItem.
     *
     * @param $title
     * @param TicketOrderField|null $field
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function make($title, TicketOrderField $field = null): static
    {
        // @phpstan-ignore-next-line
        $item = new static();
        $item->field = $field;

        $item->title($field->ticket->title);
        $item->pricePerUnit($field->ticket->price);
        $item->quantity($field->amount);
        $item->subTotalPrice($field->total);

        return $item;
    }

    /**
     * Returns true if item can be merged with current one.
     *
     * @param InvoiceItem $item
     *
     * @return bool
     */
    public function canBeMerged(InvoiceItem $item): bool
    {
        if (parent::canBeMerged($item)) {
            /** @var TicketOrderField $base */
            $base = $this->getField();
            /** @var TicketOrderField $given */
            $given = $item->getField();

            return $base->ticket_id === $given->ticket_id;
        }

        return false;
    }
}
