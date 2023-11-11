<?php

namespace App\Invoices\Items;

use App\Invoices\InvoiceItem;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrderField;

/**
 * Class SpaceItem.
 *
 * @package App\Invoices\Items
 */
class SpaceItem extends InvoiceItem
{
    /**
     * Ordered space field.
     *
     * @var SpaceOrderField
     */
    protected SpaceOrderField $field;

    public static function make(SpaceOrderField $field): static
    {
        // @phpstan-ignore-next-line
        $item = new static();
        $item->field = $field;

        $item->title($field->space->title);
        $item->pricePerUnit($field->space->price);
        $item->quantity(1);
        $item->subTotalPrice($field->total);

        return $item;
    }

    public function getDuration(): int
    {
        return $this->field->duration;
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
            /** @var SpaceOrderField $base */
            $base = $this->getField();
            /** @var SpaceOrderField $given */
            $given = $item->getField();

            return $base->space_id === $given->space_id;
        }

        return false;
    }
}
