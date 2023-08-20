<?php

namespace App\Invoices\Items;

use App\Invoices\InvoiceItem;
use App\Models\Orders\ProductOrderField;

/**
 * Class ProductItem.
 *
 * @package App\Invoices\Items
 */
class ProductItem extends InvoiceItem
{
    /**
     * Ordered product field.
     *
     * @var ProductOrderField
     */
    protected ProductOrderField $field;

    public static function make(ProductOrderField $field): static
    {
        $item = new static();
        $item->field = $field;

        $item->title($field->product->title);
        $item->quantity($field->amount);
        $item->pricePerUnit($field->price);
        $item->subTotalPrice($field->total);

        return $item;
    }

    public function getVariant(): ?string
    {
        if ($this->field->variant) {
            return implode('', [
                $this->field->variant->weight ?? '',
                $this->field->variant->weight_unit ?? '',
            ]);
        }

        return implode('', [
            $this->field->product->weight ?? '',
            $this->field->product->weight_unit ?? '',
        ]);
    }
}
