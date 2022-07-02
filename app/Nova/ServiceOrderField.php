<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;

/**
 * Class ServiceOrderField.
 *
 * @property \App\Models\Orders\ServiceOrderField $resource
 *
 * @mixin \App\Models\Orders\ServiceOrderField
 */
class ServiceOrderField extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Orders\ServiceOrderField::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
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

            BelongsTo::make('Order'),

            BelongsTo::make('Service'),

            Number::make('Amount')
                ->step(1)
                ->rules('required', 'min:1'),

            Number::make('Duration')
                ->step(5)
                ->default(0)
                ->rules('required', 'min:0'),

            Number::make('Total')
                ->exceptOnForms()
                ->readonly(),

            MorphToMany::make('Discounts', 'discounts', Discount::class),

            MorphMany::make('Comments', 'comments', Comment::class),

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
            'order' => true,
            'service' => true,
            'amount' => true,
            'duration' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
