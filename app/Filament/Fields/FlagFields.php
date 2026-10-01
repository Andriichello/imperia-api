<?php

namespace App\Filament\Fields;

use App\Enums\ProductFlag;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;

/**
 * Class FlagFields.
 *
 * Inputs for the `flags` attribute, split into tags, hotness and allergens.
 * Only the hidden `flags` field is saved: it is built from the three inputs,
 * keeping the order of the flags that stay and the ones the inputs don't know.
 */
class FlagFields
{
    /**
     * @return Field[]
     */
    public static function make(): array
    {
        return [
            // not rendered, the inputs below are the source of truth
            Hidden::make('flags')
                ->hidden()
                ->dehydratedWhenHidden()
                ->dehydrateStateUsing(fn (Get $get, mixed $state) => static::combine((array) $state, [
                    ...($get('flag_tags') ?? []),
                    $get('flag_hotness'),
                    ...($get('flag_allergens') ?? []),
                ])),
            Select::make('flag_tags')
                ->label('Tags')
                ->multiple()
                ->options(ProductFlag::getTagLabels())
                ->afterStateHydrated(fn (Select $component, Get $get) => $component->state(
                    static::pick($get('flags'), ProductFlag::getTagLabels())
                ))
                ->dehydrated(false),
            Select::make('flag_hotness')
                ->label('Hotness')
                ->placeholder('Not spicy')
                ->options(ProductFlag::getHotnessLabels())
                ->afterStateHydrated(fn (Select $component, Get $get) => $component->state(
                    static::pick($get('flags'), ProductFlag::getHotnessLabels())[0] ?? null
                ))
                ->dehydrated(false),
            CheckboxList::make('flag_allergens')
                ->label('Allergens')
                ->options(ProductFlag::getAllergenLabels())
                ->columns(4)
                ->gridDirection('row')
                ->afterStateHydrated(fn (CheckboxList $component, Get $get) => $component->state(
                    static::pick($get('flags'), ProductFlag::getAllergenLabels())
                ))
                ->dehydrated(false)
                ->columnSpanFull(),
        ];
    }

    /**
     * Flags of the given group.
     *
     * @param mixed $flags
     * @param array<string, string> $group
     *
     * @return string[]
     */
    protected static function pick(mixed $flags, array $group): array
    {
        return array_values(array_intersect((array) $flags, array_keys($group)));
    }

    /**
     * Combine the current flags with the selected ones.
     *
     * @param string[] $current
     * @param array<int, string|null> $selected
     *
     * @return string[]
     */
    protected static function combine(array $current, array $selected): array
    {
        $known = array_keys(ProductFlag::getLabels());
        $selected = array_values(array_intersect(array_filter($selected), $known));

        $kept = array_filter($current, fn ($flag) => !in_array($flag, $known) || in_array($flag, $selected));

        return array_values(array_unique([...$kept, ...$selected]));
    }
}
