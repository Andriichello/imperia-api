<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

/**
 * Class Restaurant.
 *
 * @mixin \App\Models\Restaurant
 */
class Restaurant extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Restaurant::class;

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
        'id', 'slug', 'name',
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

            MediaField::make('Media'),

            Number::make('Popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make('Slug')
                ->rules('required', 'min:1', 'max:255')
                ->creationRules('unique:restaurants,slug')
                ->updateRules('unique:restaurants,slug,{{resourceId}}'),

            Text::make('Name')
                ->rules('required', 'min:1', 'max:255'),

            Text::make('Country')
                ->rules('required', 'min:1', 'max:255'),

            Text::make('City')
                ->rules('required', 'min:1', 'max:255'),

            Text::make('Place')
                ->rules('required', 'min:1', 'max:255'),

            HasMany::make('Banquets'),

            HasMany::make('Schedules'),

            HasMany::make('Holidays'),

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
            'media' => true,
            'popularity' => true,
            'slug' => true,
            'name' => true,
            'country' => false,
            'city' => false,
            'place' => false,
            'banquets' => false,
            'schedules' => false,
            'holidays' => false,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
