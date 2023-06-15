<?php

namespace App\Nova;

use App\Nova\Options\MorphOptions;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Discount.
 *
 * @mixin \App\Models\Morphs\Discount
 */
class Discount extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Morphs\Discount::class;

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
        'id', 'title',
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

            Select::make('Target')
                ->displayUsingLabels()
                ->options(MorphOptions::discountable()),

            Text::make('Title')
                ->rules('required', 'min:1', 'max:255'),

            Text::make('Description')
                ->rules('nullable', 'min:1', 'max:255'),

            Number::make('Amount')
                ->step(0.01)
                ->rules('required', 'min:0'),

            Number::make('Percent')
                ->step(0.01)
                ->rules('required', 'min:0', 'max:100'),

            MorphMany::make('Logs', 'logs', Log::class),

            DateTime::make('Created At')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Updated At')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    /**
     * Build a "relatable" query for the given resource.
     *
     * This query determines which instances of the model may be attached to other resources.
     *
     * @param NovaRequest $request
     * @param EloquentBuilder $query
     *
     * @return EloquentBuilder
     */
    public static function relatableQuery(NovaRequest $request, $query): EloquentBuilder
    {
        $target = slugClass($request->resource());
        // @phpstan-ignore-next-line
        return parent::relatableQuery($request, $query)
            ->whereNested(function (Builder $builder) use ($target) {
                $builder->whereNull('target')
                    ->orWhere('target', $target);
            });
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
            'title' => true,
            'description' => false,
            'amount' => true,
            'percent' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
