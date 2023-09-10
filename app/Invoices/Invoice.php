<?php

namespace App\Invoices;

use App\Invoices\Items\ProductItem;
use App\Invoices\Items\ServiceItem;
use App\Invoices\Items\SpaceItem;
use App\Invoices\Items\TicketItem;
use App\Models\Menu;
use App\Models\Orders\Order;
use Illuminate\Contracts\Container\BindingResolutionException;
use LaravelDaily\Invoices\Invoice as BaseInvoice;

/**
 * Class Invoice.
 *
 * @package App\Invoices
 */
class Invoice extends BaseInvoice
{
    /**
     * Invoice order.
     *
     * @var Order
     */
    protected Order $order;

    /**
     * Menus, which should be on invoice.
     * All menus will be displayed if it's null.
     *
     * @var array|null
     */
    protected ?array $menus = null;

    /**
     * Sections, which should be on invoice.
     * All sections will be displayed if it's null.
     *
     * @var array|null
     */
    protected ?array $sections = null;

    /**
     * Create a new instance of invoice.
     *
     * @param string $name
     * @param Order|null $order
     *
     * @return Invoice
     * @throws BindingResolutionException
     */
    public static function make($name = '', ?Order $order = null): Invoice
    {
        $name = strlen($name) || $order === null
            ? $name : "receipt-{$order->id}";

        /** @var Invoice $invoice */
        $invoice = parent::make($name);
        $invoice->order = $order;

        return $invoice;
    }

    /**
     * Specify menus, which should be on invoice.
     *
     * @param array|null $menus
     *
     * @return $this
     */
    public function withMenus(?array $menus): static
    {
        $this->menus = $menus;

        return $this;
    }

    /**
     * Get menus, which should be on invoice.
     *
     * @return array|null
     */
    public function onlyMenus(): ?array
    {
        return $this->menus;
    }

    /**
     * Specify sections, which should be on invoice.
     *
     * @param array|null $sections
     *
     * @return $this
     */
    public function withSections(?array $sections): static
    {
        $this->sections = $sections;

        return $this;
    }

    /**
     * Get sections, which should be on invoice.
     *
     * @return array|null
     */
    public function onlySections(): ?array
    {
        return $this->sections;
    }

    /**
     * Get comments.
     *
     * @return array
     */
    public function getComments(): array
    {
        return $this->order->comments->toArray();
    }

    /**
     * Get date.
     *
     * @return string
     */
    public function getDate(): string
    {
        return $this->order->banquet->start_at->format('d/m/Y');
    }

    /**
     * Get start time.
     *
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->order->banquet->start_at->format('H:i');
    }

    /**
     * Get end time.
     *
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->order->banquet->end_at->format('H:i');
    }

    /**
     * Get adults amount.
     *
     * @return int|null
     */
    public function getAdultsAmount(): ?int
    {
        return $this->order->banquet->adults_amount;
    }

    /**
     * Get adult ticket price.
     *
     * @return float|null
     */
    public function getAdultTicketPrice(): ?float
    {
        return $this->order->banquet->adult_ticket_price;
    }

    /**
     * Get children amount.
     *
     * @return int|null
     */
    public function getChildrenAmount(): ?int
    {
        return $this->order->banquet->children_amount;
    }

    /**
     * Get child ticket price.
     *
     * @return float|null
     */
    public function getChildTicketPrice(): ?float
    {
        return $this->order->banquet->child_ticket_price;
    }

    /**
     * @param InvoiceItem $item
     *
     * @return string
     */
    public function itemFormattedPrice(InvoiceItem $item): string
    {
        $formatted = $this->formatCurrency($item->price_per_unit);

        if ($item->getOncePaidPrice()) {
            $hourly = $this->formatCurrency($item->getOncePaidPrice());
            return "$formatted + $hourly * hours";
        }

        return $formatted;
    }

    /**
     * @param int|null $menuId
     *
     * @return ProductItem[]
     */
    public function getProducts(?int $menuId = null): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof ProductItem) {
                if ($menuId) {
                    $skip = true;

                    foreach ($item->getMenus() ?? [] as $menu) {
                        /** @var Menu $menu */
                        if (data_get($menu, 'id') === $menuId) {
                            $skip = false;
                        }
                    }

                    if ($skip) {
                        continue;
                    }
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array<int, Menu>
     */
    public function getMenus(): array
    {
        $menus = [];

        foreach ($this->getProducts() as $item) {
            foreach ($item->getMenus()?->toArray() ?? [] as $menu) {
                $menuId = data_get($menu, 'id');
                if (key_exists($menuId, $menus)) {
                    continue;
                }

                $menus[$menuId] = $menu;
            }
        }

        return $menus;
    }

    /**
     * @return array<int, ProductItem[]>
     */
    public function getProductsByMenus(): array
    {
        $result = [];

        foreach ($this->getMenus() as $menu) {
            foreach ($this->getProducts(data_get($menu, 'id')) as $item) {
                $alreadyAdded = false;

                foreach ($result as $addedItems) {
                    if (empty($addedItems)) {
                        continue;
                    }


                    foreach ($addedItems as $addedItem) {
                        if ($addedItem === $item) {
                            $alreadyAdded = true;
                            break;
                        }
                    }
                }

                if (!$alreadyAdded) {
                    $items = data_get($result, data_get($menu, 'id'), []);
                    $items[] = $item;

                    $result[data_get($menu, 'id')] = $items;
                }
            }
        }

        return $result;
    }


    /**
     * @return SpaceItem[]
     */
    public function getSpaces(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof SpaceItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return ServiceItem[]
     */
    public function getServices(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof ServiceItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return TicketItem[]
     */
    public function getTickets(): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if ($item instanceof TicketItem) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Get total price of given items.
     *
     * @param array $items
     *
     * @return float
     */
    public function getTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            /** @var InvoiceItem $item */
            $total += $item->getTotal() ?? 0.0;
        }

        return $total;
    }
}
