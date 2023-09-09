<?php

namespace App\Nova;

use App\Nova\Options\WeightUnitOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

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
        $units = [];

        foreach (WeightUnitOptions::all() as $unit) {
            $units[$unit] = __('enum.weight_unit.' . $unit);
        }

        return [
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Number::make(__('columns.price'), 'price')
                ->step(0.01)
                ->updateRules('required', 'min:0')
                ->creationRules('required', 'min:0'),

            Text::make(__('columns.weight'), 'weight')
                ->nullable(),

            Select::make(__('columns.weight_unit'), 'weight_unit')
                ->nullable()
                ->options($units)
                ->displayUsing(fn($val) => data_get($units, $val ?? 'non-existing')),

            BelongsTo::make(__('columns.product'), 'product', Product::class),

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
                'checked' => true,
            ],
            'price' => [
                'label' => __('columns.price'),
                'checked' => true,
            ],
            'weight' => [
                'label' => __('columns.weight'),
                'checked' => true,
            ],
            'weight_unit' => [
                'label' => __('columns.weight_unit'),
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
