<?php

namespace App\Nova;

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
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Banquet'),

            Code::make('Metadata')
                ->height(50)
                ->json(),

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
            'banquet' => true,
            'metadata' => true,
            'total' => true,
            'discounted_total' => true,
            'discounts_amount' => true,
            'discounts_percent' => true,
            'spaces' => false,
            'tickets' => false,
            'products' => false,
            'services' => false,
            'discounts' => false,
            'comments' => false,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
