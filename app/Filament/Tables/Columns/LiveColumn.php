<?php

namespace App\Filament\Tables\Columns;

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
            ->disabled(fn (Model $record) => Gate::denies('update', $record));
    }
}
