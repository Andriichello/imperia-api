<?php

namespace App\Invoices\Items;

use App\Invoices\InvoiceItem;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\ServiceOrderField;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrderField;

/**
 * Class ServiceItem.
 *
 * @package App\Invoices\Items
 */
class ServiceItem extends InvoiceItem
{
    /**
     * Ordered service field.
     *
     * @var ServiceOrderField
     */
    protected ServiceOrderField $field;

    public static function make(ServiceOrderField $field): static
    {
        $item = new static();
        $item->field = $field;

        $item->title($field->service->title);
        $item->pricePerUnit($field->service->hourly_paid_price);
        $item->quantity($field->duration / 60.0);
        $item->subTotalPrice($field->total);

        return $item;
    }
}
