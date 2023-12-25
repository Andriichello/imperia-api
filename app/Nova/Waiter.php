<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Waiter.
 *
 * @mixin \App\Models\Waiter
 */
class Waiter extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Waiter::class;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'surname', 'phone', 'email',
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

            Text::make(__('columns.uuid'), 'uuid')
                ->rules('nullable', 'min:1', 'max:6'),

            Text::make(__('columns.name'), 'name')
                ->rules('required', 'min:2', 'max:50'),

            Text::make(__('columns.surname'), 'surname')
                ->rules('sometimes', 'min:0', 'max:50')
                ->fillUsing(function(NovaRequest $request, \App\Models\Waiter $waiter) {
                    $waiter->surname = $request->get('surname') ?? '';
                }),

            Text::make(__('columns.phone'), 'phone')
                ->rules('nullable', 'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/')
                ->creationRules('unique:waiters,phone')
                ->updateRules('unique:waiters,phone,{{resourceId}}'),

            Text::make(__('columns.email'), 'email')
                ->sortable()
                ->rules('nullable', 'email', 'max:254')
                ->creationRules('unique:waiters,email')
                ->updateRules('unique:waiters,email,{{resourceId}}'),

            Date::make(__('columns.birthdate'), 'birthdate')
                ->sortable()
                ->rules('nullable', 'date', 'before:today'),

            Text::make(__('columns.about'), 'about')
                ->updateRules('nullable', 'min:1', 'max:255')
                ->creationRules('nullable', 'min:1', 'max:255'),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class),

            MorphMany::make(__('columns.tips'), 'tips', Tip::class),

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
            'name' => [
                'label' => __('columns.name'),
                'checked' => true
            ],
            'surname' => [
                'label' => __('columns.surname'),
                'checked' => true
            ],
            'phone' => [
                'label' => __('columns.phone'),
                'checked' => true,
            ],
            'email' => [
                'label' => __('columns.email'),
                'checked' => true,
            ],
            'birthdate' => [
                'label' => __('columns.birthdate'),
                'checked' => true,
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
                'checked' => false
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
