<?php

namespace App\Invoices\Items;

use App\Invoices\InvoiceItem;
use App\Models\Orders\ProductOrderField;
use Illuminate\Support\Collection;

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
            $unit = $this->field->variant->weight_unit;

            if ($unit) {
                $unit = translate("enum.weight_unit.$unit", [], $unit);
            }

            return implode(' ', [
                $this->field->variant->weight ?? '',
                $unit ?? '',
            ]);
        }

        $unit = $this->field->product->weight_unit;

        if ($unit) {
            $unit = translate("enum.weight_unit.$unit", [], $unit);
        }

        return implode(' ', [
            $this->field->product->weight ?? '',
            $unit ?? '',
        ]);
    }

    public function getMenus(): ?Collection
    {
        return $this->field?->product?->menus;
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
            /** @var ProductOrderField $base */
            $base = $this->getField();
            /** @var ProductOrderField $given */
            $given = $item->getField();

            return $base->product_id === $given->product_id
                && $base->variant_id === $given->variant_id;
        }

        return false;
    }
}
