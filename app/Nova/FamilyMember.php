<?php

namespace App\Nova;

use App\Nova\Options\FamilyRelationOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

/**
 * Class FamilyMember.
 *
 * @mixin \App\Models\FamilyMember
 */
class FamilyMember extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\FamilyMember::class;

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
        'id', 'name', 'relation',
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

            Date::make('Birthdate')
                ->sortable()
                ->rules('required', 'date', 'before:today'),

            Select::make('Relation')
                ->displayUsingLabels()
                ->rules('required')
                ->options(FamilyRelationOptions::all()),

            BelongsTo::make('Relative', 'relative', Customer::class),

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
            'birthdate' => true,
            'relation' => true,
            'relative' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
