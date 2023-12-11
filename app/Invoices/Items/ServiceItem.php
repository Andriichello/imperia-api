<?php

namespace App\Invoices\Items;

use App\Invoices\InvoiceItem;
use App\Models\Orders\ServiceOrderField;

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

    /**
     * Make a new instance of ServiceItem.
     *
     * @param $title
     * @param ServiceOrderField|null $field
     *
     * @return static
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function make($title, ServiceOrderField $field = null): static
    {
        // @phpstan-ignore-next-line
        $item = new static();
        $item->field = $field;

        $item->title($field->service->title);
        $item->pricePerUnit($field->service->hourly_paid_price);
        $item->quantity($field->duration / 60.0);
        $item->subTotalPrice($field->total);

        return $item;
    }

    public function getDuration(): int
    {
        return $this->field->duration;
    }

    public function getOncePaidPrice(): ?float
    {
        return $this->field->service->once_paid_price;
    }

    public function getHourlyPaidPrice(): ?float
    {
        return $this->field->service->hourly_paid_price;
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
            /** @var ServiceOrderField $base */
            $base = $this->getField();
            /** @var ServiceOrderField $given */
            $given = $item->getField();

            return $base->service_id === $given->service_id;
        }

        return false;
    }
}
