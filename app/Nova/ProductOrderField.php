<?php

namespace App\Nova;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Michielfb\Time\Time;

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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            BelongsTo::make(__('columns.order'), 'order', Order::class),

            BelongsTo::make(__('columns.product'), 'product', Product::class),

            BelongsTo::make(__('columns.variant'), 'variant', ProductVariant::class)
                ->nullable(),

            Number::make(__('columns.amount'), 'amount')
                ->step(1)
                ->rules('required', 'min:1'),

            Text::make(__('columns.serve_at'), 'serve_at')
                ->resolveUsing(fn() => $this->serve_at ? (new Carbon($this->serve_at))->format('H:i') : $this->serve_at)
                ->nullable()
                ->rules('sometimes', 'nullable', 'date_format:H:i'),

            Number::make(__('columns.total'))
                ->exceptOnForms()
                ->readonly(),

            // MorphToMany::make(__('columns.discounts'), 'discounts', Discount::class),

            MorphMany::make(__('columns.comments'), 'comments', Comment::class),

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
            'order' => [
                'label' => __('columns.order'),
                'checked' => true,
            ],
            'product' => [
                'label' => __('columns.product'),
                'checked' => true,
            ],
            'variant' => [
                'label' => __('columns.variant'),
                'checked' => true,
            ],
            'amount' => [
                'label' => __('columns.amount'),
                'checked' => true,
            ],
            'serve_at' => [
                'label' => __('columns.serve_at'),
                'checked' => true,
            ],
            'total' => [
                'label' => __('columns.total'),
                'checked' => false,
            ],
            'created_at' => [
                'label' => __('columns.created_at'),
                'checked' => false,
            ],
            'updated_at' => [
                'label' => __('columns.updated_at'),
                'checked' => false,
            ],
        ];
    }
}
