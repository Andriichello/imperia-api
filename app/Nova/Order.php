<?php

namespace App\Nova;

use App\Nova\Actions\CalculateTotals;
use App\Nova\Actions\GenerateInvoice;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
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
            ID::make()->sortable(),

            BelongsToMany::make('Banquets'),

            Code::make('Metadata')
                ->resolveUsing(fn() => json_encode(json_decode($this->metadata), JSON_PRETTY_PRINT))
                ->autoHeight()
                ->json()
                ->onlyOnDetail()
                ->readonly(),

            Number::make('Total')
                ->resolveUsing(fn() => data_get($this->totals, 'all'))
                ->exceptOnForms()
                ->readonly(),

            HasMany::make('Spaces', 'spaces', SpaceOrderField::class),

            HasMany::make('Tickets', 'tickets', TicketOrderField::class),

            HasMany::make('Products', 'products', ProductOrderField::class),

            HasMany::make('Services', 'services', ServiceOrderField::class),

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
            'banquets' => true,
            'total' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
