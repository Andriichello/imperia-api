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
 * Class ProductOrderField.
 *
 * @property \App\Models\Orders\ProductOrderField $resource
 *
 * @mixin \App\Models\Orders\ProductOrderField
 */
class ProductOrderField extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Orders\ProductOrderField::class;

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

            BelongsTo::make('Product'),

            Number::make('Amount')
                ->step(1)
                ->rules('required', 'min:1'),

            Number::make('Total')
                ->exceptOnForms()
                ->readonly(),

            Number::make('Discounted Total')
                ->exceptOnForms()
                ->readonly(),

            Number::make('Discounts Amount')
                ->exceptOnForms()
                ->readonly(),

            Number::make('Discounts Percent')
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
            'product' => true,
            'amount' => true,
            'discounts' => false,
            'discounted_total' => true,
            'discounts_amount' => true,
            'discounts_percent' => true,
            'comments' => false,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
