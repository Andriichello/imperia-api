<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Text;

/**
 * Class Customer.
 *
 * @mixin \App\Models\Customer
 */
class Customer extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Customer::class;

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

            Text::make(__('columns.name'), 'name')
                ->rules('required', 'min:2', 'max:50'),

            Text::make(__('columns.surname'), 'surname')
                ->rules('required', 'min:2', 'max:50'),

            Text::make(__('columns.phone'), 'phone')
                ->rules('required', 'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/')
                ->creationRules('unique:customers,phone')
                ->updateRules('unique:customers,phone,{{resourceId}}'),

            Text::make(__('columns.email'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:customers,email')
                ->updateRules('unique:customers,email,{{resourceId}}'),

            Date::make(__('columns.birthdate'), 'birthdate')
                ->sortable()
                ->rules('required', 'date', 'before:today'),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class),

            HasMany::make(__('columns.family_members'), 'familyMembers', FamilyMember::class),

            MorphMany::make(__('columns.comments'), 'comments', Comment::class),

            MorphMany::make(__('columns.logs'), 'logs', Log::class),

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
