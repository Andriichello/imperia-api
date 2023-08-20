<?php

namespace App\Invoices;

use App\Models\BaseModel;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\ServiceOrderField;
use App\Models\Orders\SpaceOrderField;
use LaravelDaily\Invoices\Classes\InvoiceItem as BaseItem;

/**
 * Class InvoiceItem.
 *
 * @package App\Invoices
 */
class InvoiceItem extends BaseItem
{
    /**
     * Get field.
     *
     * @return BaseModel
     */
    public function getField(): BaseModel
    {
        // @phpstan-ignore-next-line
        return $this->field;
    }

    /**
     * Get ordered field price.
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        // @phpstan-ignore-next-line
        return $this->field?->price;
    }

    /**
     * Get ordered field variant.
     *
     * @return string|null
     */
    public function getVariant(): ?string
    {
        return null;
    }

    /**
     * Get ordered field once price.
     *
     * @return float|null
     */
    public function getOncePaidPrice(): ?float
    {
        return $this->field?->once_paid_price;
    }

    /**
     * Get ordered field amount.
     *
     * @return int|null
     */
    public function getAmount(): ?int
    {
        // @phpstan-ignore-next-line
        return $this->field?->amount;
    }

    /**
     * Get ordered total price.
     *
     * @return float|null
     */
    public function getTotal(): ?float
    {
        // @phpstan-ignore-next-line
        return $this->field?->total;
    }

    /**
     * Get ordered field comments.
     *
     * @return array
     */
    public function getComments(): array
    {
        // @phpstan-ignore-next-line
        return $this->field?->comments?->toArray() ?? [];
    }
}
