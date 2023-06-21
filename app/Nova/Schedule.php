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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Select::make(__('columns.weekday'), 'weekday')
                ->required()
                ->options(WeekdayOptions::all()),

            Number::make(__('columns.beg_hour'), 'beg_hour')
                ->required()
                ->min(0)
                ->max(23),

            Number::make(__('columns.beg_minute'), 'beg_minute')
                ->default(0)
                ->required()
                ->min(0)
                ->max(59),

            Number::make(__('columns.end_hour'), 'end_hour')
                ->required()
                ->min(0)
                ->max(23),

            Number::make(__('columns.end_minute'), 'end_minute')
                ->default(0)
                ->required()
                ->min(0)
                ->max(59),

            Boolean::make(__('columns.is_cross_date'), 'is_cross_date')
                ->exceptOnForms()
                ->readonly(),

            DateTime::make(__('columns.closest_date'), 'closest_date')
                ->exceptOnForms()
                ->readonly(),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class)
                ->nullable(),

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
                'checked' => true
            ],
            'weekday' => [
                'label' => __('columns.weekday'),
                'checked' => true,
            ],
            'beg_hour' => [
                'label' => __('columns.beg_hour'),
                'checked' => true,
            ],
            'beg_minute' => [
                'label' => __('columns.beg_minute'),
                'checked' => true,
            ],
            'end_hour' => [
                'label' => __('columns.end_hour'),
                'checked' => true,
            ],
            'end_minute' => [
                'label' => __('columns.end_minute'),
                'checked' => true,
            ],
            'is_cross_date' => [
                'label' => __('columns.is_cross_date'),
                'checked' => false,
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
                'checked' => true,
            ],
            'closest_date' => [
                'label' => __('columns.closest_date'),
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
