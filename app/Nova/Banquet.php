<?php

namespace App\Nova;

use App\Enums\BanquetState;
use App\Nova\Actions\CalculateTotals;
use App\Nova\Actions\GenerateInvoice;
use App\Nova\Options\BanquetStateOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

/**
 * Class Banquet.
 *
 * @property \App\Models\Banquet $resource
 *
 * @mixin \App\Models\Banquet
 */
class Banquet extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Banquet::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title', 'description',
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

            Select::make(__('columns.state'), 'state')
                ->rules('required')
                ->default(BanquetState::New)
                ->options(BanquetStateOptions::available($request, $this->resource)),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class)
                ->nullable(),

            BelongsTo::make(__('columns.creator'), 'creator', User::class)
                ->withMeta(['belongsToId' => data_get($this->creator ?? $request->user(), 'id')]),

            BelongsTo::make(__('columns.customer'), 'customer', Customer::class),

            HasOne::make(__('columns.order'), 'order', Order::class),

            Text::make(__('columns.title'), 'title')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Text::make(__('columns.description'), 'description')
                ->rules('nullable', 'min:1', 'max:255'),

            Number::make(__('columns.advance_amount'), 'advance_amount')
                ->default(0.0)
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Text::make(__('columns.total'), 'total')
                ->resolveUsing(fn() => data_get($this->totals, 'all'))
                ->exceptOnForms()
                ->readonly(),

            DateTime::make(__('columns.start_at'), 'start_at')
                ->sortable()
                ->rules('required', 'date'),

            DateTime::make(__('columns.end_at'), 'end_at')
                ->sortable()
                ->rules('required', 'date', 'after:start_at'),

            DateTime::make(__('columns.paid_at'), 'paid_at')
                ->sortable()
                ->rules('nullable', 'date', 'after:start_at'),

            MorphMany::make(__('columns.comments'), 'comments', Comment::class),

            MorphMany::make(__('columns.logs'), 'logs', Log::class),

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
            'state' => [
                'label' => __('columns.state'),
                'checked' => true,
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
                'checked' => false,
            ],
            'creator' => [
                'label' => __('columns.creator'),
                'checked' => false,
            ],
            'customer' => [
                'label' => __('columns.customer'),
                'checked' => true,
            ],
            'title' => [
                'label' => __('columns.title'),
                'checked' => false
            ],
            'description' => [
                'label' => __('columns.description'),
                'checked' => false
            ],
            'advance_amount' => [
                'label' => __('columns.advance_amount'),
                'checked' => true,
            ],
            'total' => [
                'label' => __('columns.total'),
                'checked' => true,
            ],
            'start_at' => [
                'label' => __('columns.start_at'),
                'checked' => true,
            ],
            'end_at' => [
                'label' => __('columns.end_at'),
                'checked' => true,
            ],
            'paid_at' => [
                'label' => __('columns.paid_at'),
                'checked' => false,
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
