<?php

namespace App\Nova;

use Illuminate\Http\Request;
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
            ID::make()->sortable(),

            Text::make('Name')
                ->rules('required', 'min:2', 'max:50'),

            Text::make('Surname')
                ->rules('required', 'min:2', 'max:50'),

            Text::make('Phone')
                ->rules('required', 'regex:/(\+?[0-9]{1,2})?[0-9]{10,12}/')
                ->creationRules('unique:customers,phone')
                ->updateRules('unique:customers,phone,{{resourceId}}'),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:customers,email')
                ->updateRules('unique:customers,email,{{resourceId}}'),

            Date::make('Birthdate')
                ->sortable()
                ->rules('required', 'date', 'before:today'),

            HasMany::make('Family Members', 'familyMembers'),

            MorphMany::make('Comments', 'comments', Comment::class),

            MorphMany::make('Logs', 'logs', Log::class),

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
            'surname' => true,
            'phone' => true,
            'email' => true,
            'birthdate' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
