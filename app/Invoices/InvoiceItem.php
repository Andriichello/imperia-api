<?php

namespace App\Invoices;

use App\Models\BaseModel;
use Exception;
use Illuminate\Support\Arr;
use LaravelDaily\Invoices\Classes\InvoiceItem as BaseItem;

/**
 * Class InvoiceItem.
 *
 * @package App\Invoices
 */
class InvoiceItem extends BaseItem
{
    /**
     * Overrides field's comments.
     *
     * @var array|null
     */
    protected ?array $comments = null;

    /**
     * Overrides field's time of serving.
     *
     * @var string|null
     */
    protected ?string $timeOfServing = null;

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
     * Set ordered field comments.
     *
     * @param array|null $comments
     *
     * @return static
     */
    public function setComments(?array $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get ordered field comments.
     *
     * @return array
     */
    public function getComments(): array
    {
        // @phpstan-ignore-next-line
        return $this->comments ?? ($this->field?->comments?->toArray() ?? []);
    }

    /**
     * Get requested time of serving.
     *
     * @return string|null
     */
    public function getTimeOfServing(): ?string
    {
        if (is_string($this->timeOfServing)) {
            return empty($this->timeOfServing) ? null : $this->timeOfServing;
        }

        $times = [];

        foreach ($this->getComments() as $comment) {
            $regex = '/\d{1,2}:\d{2}/';
            $text = data_get($comment, 'text');

            $matches = matches($regex, $text);

            if (!empty($matches)) {
                $times = array_merge($times, $matches);
                break;
            }
        }

        $timeOfServing = Arr::first($times);

        return $this->timeOfServing = empty($timeOfServing) ? '' : $timeOfServing;
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
        if (get_class($this) !== get_class($item)) {
            return false;
        }

        $base = $this->getField();
        $given = $item->getField();

        if (get_class($base) !== get_class($given)) {
            return false;
        }

        return empty($this->getComments()) && empty($item->getComments());
    }

    /**
     * Merges current item with the given one.
     *
     * @param InvoiceItem $item
     *
     * @return static
     * @throws Exception
     */
    public function mergeWith(InvoiceItem $item): static
    {
        if (!$this->canBeMerged($item)) {
            throw new Exception('Can\'t merge with given item.');
        }

        $this->quantity($this->quantity + $item->quantity);
        $this->subTotalPrice($this->sub_total_price + $item->sub_total_price);

        return $this;
    }

    /**
     * Compare given items to figure out their place in an ordered list.
     *
     * @param InvoiceItem $itemOne
     * @param InvoiceItem $itemTwo
     *
     * @return int
     */
    public static function compare(InvoiceItem $itemOne, InvoiceItem $itemTwo): int
    {
        $timeOne = $itemOne->getTimeOfServing();
        $timeTwo = $itemTwo->getTimeOfServing();

        if (!$timeOne) {
            return $timeTwo !== null ? 1 : -1;
        } elseif (!$timeTwo) {
            return -1;
        }

        [$hourOne, $minuteOne] = explode(':', $timeOne);
        [$hourTwo, $minuteTwo] = explode(':', $timeTwo);

        if ($hourOne > $hourTwo) {
            return 1;
        } elseif ($hourOne < $hourTwo) {
            return -1;
        } elseif ($minuteOne > $minuteTwo) {
            return 1;
        } elseif ($minuteOne < $minuteTwo) {
            return -1;
        }

        return 0;
    }
}
