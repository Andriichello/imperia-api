<?php

namespace App\Filament\Tables\Columns;

use App\Models\DishVariant;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Class LiveColumn.
 *
 * Toggle, which shows the opposite of the given attribute (`is_hidden` by default,
 * `archived` of variants), and hides or publishes the record right from the table
 * (for users, who can update it).
 */
class LiveColumn extends ToggleColumn
{
    public static function make(string $name = 'is_hidden'): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Live')
            ->alignCenter()
            ->onColor('success')
            ->offColor('danger')
            ->getStateUsing(fn (Model $record) => !$record->getAttribute($this->getName()))
            ->updateStateUsing(function (Model $record, mixed $state) {
                $record->update([$this->getName() => !$state]);

                return (bool) $state;
            })
            ->disabled(fn (Model $record) => Gate::denies('update', $record) || static::isLastSize($record))
            ->tooltip(fn (Model $record) => static::isLastSize($record) ? DishVariant::LAST_SIZE_MESSAGE : null);
    }

    /**
     * Whether the record is the only size of its dish, which guests see (it can't be hidden).
     *
     * @param Model $record
     *
     * @return bool
     */
    protected static function isLastSize(Model $record): bool
    {
        return $record instanceof DishVariant && $record->isLastShown();
    }
}
