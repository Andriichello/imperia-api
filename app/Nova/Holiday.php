<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
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
        'id', 'name', 'date',
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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Text::make('Name')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Text::make(__('columns.description'), 'description')
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

            HasMany::make('Restaurant', 'restaurant', Restaurant::class)
                ->default(fn() => $request->user()->restaurant_id),

            DateTime::make(__('columns.created_at'), 'created_at')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make(__('columns.updated_at' ), 'updated_at')
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
            'id' => [
                'label' => __('columns.id'),
                'checked' => true,
            ],
            'name' => [
                'label' => __('columns.name'),
                'checked' => true,
            ],
            'description' => [
                'label' => __('columns.description'),
                'checked' => false,
            ],
            'day' => [
                'label' => __('columns.day'),
                'checked' => true,
            ],
            'month' => [
                'label' => __('columns.month'),
                'checked' => true,
            ],
            'year' => [
                'label' => __('columns.year'),
                'checked' => true,
            ],
            'closest_date' => [
                'label' => __('columns.closest_date'),
                'checked' => true,
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
                'checked' => true,
            ],
            'created_at' => [
                'label' => __('columns.created_at'),
                'checked' => false
            ],
            'updated_at' => [
                'label' => __('columns.updated_at'),
                'checked' => false
            ],
        ];
    }
}
