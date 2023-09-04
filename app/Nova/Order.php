<?php

namespace App\Nova;

use App\Nova\Actions\CalculateTotals;
use App\Nova\Actions\GenerateInvoice;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;

/**
 * Class Order.
 *
 * @property \App\Models\Orders\Order $resource
 *
 * @mixin \App\Models\Orders\Order
 */
class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Orders\Order::class;

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
     * Get the actions available for the resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function actions(Request $request): array
    {
        return [
            new CalculateTotals(),
            new GenerateInvoice(),
        ];
    }

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

            BelongsTo::make(__('columns.banquet'), 'banquet', Banquet::class),

            Code::make(__('columns.metadata'), 'metadata')
                ->resolveUsing(fn() => json_encode(json_decode($this->metadata), JSON_PRETTY_PRINT))
                ->autoHeight()
                ->json()
                ->onlyOnDetail()
                ->readonly(),

            Number::make(__('columns.total'), 'total')
                ->resolveUsing(fn() => data_get($this->totals, 'all'))
                ->exceptOnForms()
                ->readonly(),

            HasMany::make(__('columns.spaces'), 'spaces', SpaceOrderField::class),

            HasMany::make(__('columns.tickets'), 'tickets', TicketOrderField::class),

            HasMany::make(__('columns.products'), 'products', ProductOrderField::class),

            HasMany::make(__('columns.services'), 'services', ServiceOrderField::class),

            MorphToMany::make(__('columns.discounts'), 'discounts', Discount::class),

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
                'checked' => true
            ],
            'banquets' => [
                'label' => __('columns.banquets'),
                'checked' => true,
            ],
            'total' => [
                'label' => __('columns.total'),
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
