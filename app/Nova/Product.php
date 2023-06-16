<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use App\Enums\WeightUnit;
use App\Models\Scopes\ArchivedScope;
use App\Nova\Options\WeightUnitOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Product.
 *
 * @mixin \App\Models\Product
 */
class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Product::class;

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
     * Build an "index" query for the given resource.
     *
     * @param NovaRequest $request
     * @param Builder $query
     *
     * @return Builder
     */
    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        /** @var User $user */
        $user = $request->user();
        if ($user->isAdmin()) {
            $query->withoutGlobalScope(ArchivedScope::class);
        }

        return $query;
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

            HasMany::make('Variants', 'variants', ProductVariant::class),

            BelongsToMany::make('Menus'),

            Boolean::make('Active')
                ->resolveUsing(fn() => !$this->archived)
                ->exceptOnForms(),

            Boolean::make('Archived')
                ->onlyOnForms()
                ->default(fn() => false),

            MediaField::make('Media'),

            Number::make('Popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make('Title')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Text::make('Badge')
                ->updateRules('nullable', 'min:1', 'max:25')
                ->creationRules('nullable', 'min:1', 'max:25'),

            Text::make('Description')
                ->rules('nullable', 'min:1', 'max:255'),

            Number::make('Price')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Number::make('Weight')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Select::make('Weight Unit')
                ->options(WeightUnitOptions::all())
                ->default(WeightUnit::Gram),

            BelongsToMany::make('Restaurants'),

            MorphToMany::make('Categories'),

            BelongsToMany::make('Restaurants'),

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
            'active' => true,
            'media' => true,
            'popularity' => true,
            'title' => true,
            'badge' => false,
            'description' => false,
            'price' => true,
            'weight' => true,
            'weight_unit' => true,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
