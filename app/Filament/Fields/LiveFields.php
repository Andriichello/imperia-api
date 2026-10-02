<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;

/**
 * Class LiveFields.
 *
 * A "Live" toggle for the `archived` attribute, like the Live column in the tables.
 * Only the hidden `archived` field is saved: it is the opposite of the toggle.
 */
class LiveFields
{
    /**
     * @return Field[]
     */
    public static function make(): array
    {
        return [
            // not rendered, the toggle below is the source of truth
            Hidden::make('archived')
                ->hidden()
                ->dehydratedWhenHidden()
                ->default(false)
                ->dehydrateStateUsing(fn (Get $get) => !$get('live')),
            Toggle::make('live')
                ->label('Live')
                ->helperText('Shown on the website. Turn off to hide it without deleting it.')
                ->default(true)
                ->afterStateHydrated(fn (Toggle $component, Get $get) => $component->state(!$get('archived')))
                ->dehydrated(false),
        ];
    }
}
