<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use DateTimeZone;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Timezone;

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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            MediaField::make(__('columns.media'), 'media'),

            Number::make(__('columns.popularity'), 'popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make(__('columns.slug'), 'slug')
                ->rules('required', 'min:1', 'max:255')
                ->creationRules('unique:restaurants,slug')
                ->updateRules('unique:restaurants,slug,{{resourceId}}'),

            Text::make(__('columns.title'), 'name')
                ->rules('required', 'min:1', 'max:255'),

            Text::make(__('columns.country'), 'country')
                ->rules('required', 'min:1', 'max:255'),

            Text::make(__('columns.city'), 'city')
                ->rules('required', 'min:1', 'max:255'),

            Text::make(__('columns.place'), 'place')
                ->rules('required', 'min:1', 'max:255'),

            Timezone::make(__('columns.timezone'), 'timezone')
                ->default(DateTimeZone::EUROPE),

            HasMany::make(__('columns.banquets'), 'banquets', Banquet::class),

            HasMany::make(__('columns.schedules'), 'schedules', Schedule::class),

            HasMany::make(__('columns.holidays'), 'holidays', Holiday::class),

            DateTime::make(__('columns.created_at'), 'created_at')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make(__('columns.updated_at'), 'updated_at')
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
            'media' => [
                'label' => __('columns.media'),
                'checked' => true,
            ],
            'popularity' => [
                'label' => __('columns.popularity'),
                'checked' => true,
            ],
            'slug' => [
                'label' => __('columns.slug'),
                'checked' => true,
            ],
            'name' => [
                'label' => __('columns.title'),
                'checked' => true,
            ],
            'country' => [
                'label' => __('columns.country'),
                'checked' => false,
            ],
            'city' => [
                'label' => __('columns.city'),
                'checked' => false,
            ],
            'place' => [
                'label' => __('columns.place'),
                'checked' => false,
            ],
            'timezone' => [
                'label' => __('columns.timezone'),
                'checked' => false,
            ],
            'created_at' => [
                'label' => __('columns.created_at'),
                'checked' => false,
            ],
            'updated_at' => [
                'label' => __('columns.updated_at'),
                'checked' => false,
            ],
        ];
    }
}
