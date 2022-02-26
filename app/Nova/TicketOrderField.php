<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Jagdeepbanga\NovaDateTime\NovaDateTime;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;

/**
 * Class TicketOrderField.
 *
 * @property \App\Models\Orders\TicketOrderField $resource
 *
 * @mixin \App\Models\Orders\TicketOrderField
 */
class TicketOrderField extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Orders\TicketOrderField::class;

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

            BelongsTo::make('Ticket'),

            Number::make('Amount')
                ->step(1)
                ->rules('required', 'min:1'),

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
            'ticket' => true,
            'amount' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
