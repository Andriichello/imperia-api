<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

/**
 * Class LiveFields.
 *
 * A "Live" toggle for the given attribute (`is_hidden` of menus, categories and dishes,
 * `archived` of variants), like the Live column in the tables. Only the hidden field
 * of that attribute is saved: it is the opposite of the toggle.
 */
class LiveFields
{
    /**
     * @param string $column the attribute, which is the opposite of the toggle
     *
     * @return Field[]
     */
    public static function make(string $column = 'is_hidden'): array
    {
        return [
            // not rendered, the toggle below is the source of truth
            Hidden::make($column)
                ->hidden()
                ->dehydratedWhenHidden()
                ->default(false)
                ->dehydrateStateUsing(fn (Get $get) => !$get('live')),
            Toggle::make('live')
                ->label('Live')
                ->helperText('Shown on the website. Turn off to hide it without deleting it.')
                ->default(true)
                ->afterStateHydrated(fn (Toggle $component, Get $get) => $component->state(!$get($column)))
                ->dehydrated(false),
        ];
    }
}
