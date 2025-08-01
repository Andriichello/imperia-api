<?php

namespace App\Invoices;

use App\Invoices\Items\ProductItem;
use App\Invoices\Items\ServiceItem;
use App\Invoices\Items\SpaceItem;
use App\Invoices\Items\TicketItem;
use App\Models\BaseModel;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\ServiceOrderField;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrderField;
use Exception;

/**
 * Class InvoiceItemFactory.
 *
 * @package App\Invoices
 */
class InvoiceItemFactory
{
    /**
     * @param BaseModel $field
     *
     * @return InvoiceItem
     * @throws Exception
     */
    public static function fromField(BaseModel $field): InvoiceItem
    {
        if ($field instanceof SpaceOrderField) {
            $item = SpaceItem::make('space', $field);
        }
        if ($field instanceof TicketOrderField) {
            $item = TicketItem::make('ticket', $field);
        }
        if ($field instanceof ServiceOrderField) {
            $item = ServiceItem::make('service', $field);
        }
        if ($field instanceof ProductOrderField) {
            $item = ProductItem::make('product', $field);
        }

        if (!isset($item)) {
            $message = 'Making InvoiceItem from ' . $field::class
                . ' is not implemented.';

            throw new Exception($message);
        }

        return $item;
    }

    /**
     * Make invoice item from space order field.
     *
     * @param SpaceOrderField $field
     *
     * @return InvoiceItem
     */
    protected static function fromSpace(SpaceOrderField $field): InvoiceItem
    {
        $item = new InvoiceItem();

        $item->title($field->space->title);
        $item->pricePerUnit($field->space->price);
        $item->subTotalPrice($field->total);

        return $item;
    }

    /**
     * Make invoice item from service order field.
     *
     * @param ServiceOrderField $field
     *
     * @return InvoiceItem
     */
    protected static function fromService(ServiceOrderField $field): InvoiceItem
    {
        $item = new InvoiceItem();

        $item->title($field->service->title);
        // $item->oncePaidPrice($field->service->once_paid_price);
        $item->pricePerUnit($field->service->hourly_paid_price);
        $item->quantity($field->duration / 60.0);
        $item->units('hours');
        $item->subTotalPrice($field->total);

        return $item;
    }

    /**
     * Make invoice item from ticket order field.
     *
     * @param TicketOrderField $field
     *
     * @return InvoiceItem
     */
    protected static function fromTicket(TicketOrderField $field): InvoiceItem
    {
        $item = new InvoiceItem();

        $item->title($field->ticket->title);
        $item->pricePerUnit($field->ticket->price);
        $item->quantity($field->amount);
        $item->subTotalPrice($field->total);

        return $item;
    }
}
