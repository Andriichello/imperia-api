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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

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
            'birthdate' => [
                'label' => __('columns.birthdate'),
                'checked' => true,
            ],
            'relation' => [
                'label' => __('columns.relation'),
                'checked' => true,
            ],
            'relative' => [
                'label' => __('columns.relative'),
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
