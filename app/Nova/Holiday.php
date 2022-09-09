<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

/**
 * Class Holiday.
 *
 * @mixin \App\Models\Holiday
 */
class Holiday extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Holiday::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'day',
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

            Text::make('Name')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Text::make('Description')
                ->updateRules('nullable', 'min:1', 'max:255')
                ->creationRules('nullable', 'min:1', 'max:255'),

            Number::make('Day')
                ->required()
                ->min(1)
                ->max(31),

            Number::make('Month')
                ->nullable()
                ->min(1)
                ->max(12),

            Number::make('Year')
                ->nullable()
                ->min(2000),

            DateTime::make('Closest Date')
                ->exceptOnForms()
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
            'name' => true,
            'description' => true,
            'day' => true,
            'month' => true,
            'year' => true,
            'restaurant' => true,
            'closest_date' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
