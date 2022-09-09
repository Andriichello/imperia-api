<?php

namespace App\Nova;

use App\Nova\Options\WeekdayOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;

/**
 * Class Schedule.
 *
 * @mixin \App\Models\Schedule
 */
class Schedule extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Schedule::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'weekday';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'weekday',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Select::make('Weekday')
                ->required()
                ->options(WeekdayOptions::all()),

            Number::make('Beg Hour')
                ->required()
                ->min(0)
                ->max(23),

            Number::make('Beg Minute')
                ->default(0)
                ->required()
                ->min(0)
                ->max(59),

            Number::make('End Hour')
                ->required()
                ->min(0)
                ->max(23),

            Number::make('End Minute')
                ->default(0)
                ->required()
                ->min(0)
                ->max(59),

            Boolean::make('Is Cross Date')
                ->readonly(),

            DateTime::make('Closest Date')
                ->readonly(),

            BelongsTo::make('Restaurant', 'restaurant', Restaurant::class)
                ->nullable(),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    /**
     * Get columns filter fields.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function columnsFilterFields(Request $request): array
    {
        return [
            'id' => true,
            'weekday' => true,
            'beg_hour' => true,
            'beg_minute' => true,
            'end_hour' => true,
            'end_minute' => true,
            'is_cross_date' => false,
            'restaurant' => true,
            'closest_date' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
