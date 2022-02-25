<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

/**
 * Class Service.
 *
 * @mixin \App\Models\Service
 */
class Service extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Service::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title', 'description',
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

            Text::make('Title')
                ->updateRules('sometimes', 'min:1', 'max:50')
                ->creationRules('required', 'min:1', 'max:50'),

            Text::make('Description')
                ->rules('nullable', 'min:1', 'max:255'),

            Number::make('Once Paid Price')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Number::make('Hourly Paid Price')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Boolean::make('Archived')
                ->default(fn() => false),

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
            'title' => true,
            'description' => false,
            'once_paid_price' => true,
            'hourly_paid_price' => true,
            'archived' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
