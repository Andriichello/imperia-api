<?php

namespace App\Nova;

use App\Enums\WeightUnit;
use App\Nova\Options\WeightUnitOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;

/**
 * Class ProductVariant.
 *
 * @mixin \App\Models\ProductVariant
 */
class ProductVariant extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\ProductVariant::class;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->weight . $this->weight_unit;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'product_id', 'weight', 'weight_unit',
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

            Number::make('Price')
                ->step(0.01)
                ->updateRules('required', 'min:0')
                ->creationRules('required', 'min:0'),

            Number::make('Weight')
                ->step(0.01)
                ->rules('required', 'min:0'),

            Select::make('Weight Unit')
                ->options(WeightUnitOptions::all())
                ->default(WeightUnit::Gram),

            BelongsTo::make('Product'),

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
            'price' => true,
            'weight' => true,
            'weight_unit' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
