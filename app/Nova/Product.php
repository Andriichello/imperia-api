<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use App\Enums\WeightUnit;
use App\Models\Scopes\ArchivedScope;
use App\Nova\Options\WeightUnitOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
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
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Product.
 *
 * @mixin \App\Models\Product
 *
 * @property \App\Models\Product $resource
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
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $title = '';

        if (!empty($this->resource->slug)) {
            $title = "{$this->resource->slug} - ";
        }

        return $title . $this->resource->title;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'slug', 'title', 'description',
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
        parent::indexQuery($request, $query);

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
        /** @var \App\Models\User $user */
        $user = $request->user();
        $resourceId = $request->route('resourceId');

        $slugValidationRules = [
            'required',
            Rule::unique('products', 'slug')
                ->where(function ($query) use ($user, $resourceId) {
                    if ($resourceId) {
                        $query->where('id', '!=', $resourceId);
                    }

                    if ($user->restaurant_id) {
                        $query->where('restaurant_id', $user->restaurant_id);
                    }
                }),
        ];

        $units = [];

        foreach (WeightUnitOptions::all() as $unit) {
            $units[$unit] = __('enum.weight_unit.' . $unit);
        }

        return [
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Text::make(__('columns.slug'), 'slug')
                ->rules($slugValidationRules),

            Boolean::make(__('columns.active'))
                ->resolveUsing(fn() => !$this->archived)
                ->exceptOnForms(),

            Boolean::make(__('columns.archived'), 'archived')
                ->onlyOnForms()
                ->default(fn() => false),

            MediaField::make(__('columns.media'), 'media'),

            Number::make(__('columns.popularity'), 'popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make(__('columns.title'), 'title')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Text::make(__('columns.badge'), 'badge')
                ->updateRules('nullable', 'min:1', 'max:25')
                ->creationRules('nullable', 'min:1', 'max:25'),

            Textarea::make(__('columns.description'), 'description')
                ->rules('nullable', 'min:1'),

            Number::make(__('columns.price'), 'price')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Text::make(__('columns.weight'), 'weight')
                ->nullable(),

            Select::make(__('columns.weight_unit'), 'weight_unit')
                ->nullable()
                ->options($units)
                ->displayUsing(fn($val) => data_get($units, $val ?? 'non-existing')),

            HasMany::make(__('columns.variants'), 'variants', ProductVariant::class),

            BelongsToMany::make(__('columns.menus'), 'menus', Menu::class),

            MorphToMany::make(__('columns.categories'), 'categories', Category::class),

            MorphToMany::make(__('columns.tags'), 'tags', Tag::class),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class)
                ->default(fn() => $user->restaurant_id),

            MorphMany::make(__('columns.alterations'), 'alterations', Alteration::class),

            MorphMany::make(__('columns.logs'), 'logs', Log::class),

            DateTime::make(__('columns.created_at'), 'created_at')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make(__('columns.updated_at'), 'updated_at')
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
            'slug' => [
                'label' => __('columns.slug'),
                'checked' => true,
            ],
            'media' => [
                'label' => __('columns.media'),
                'checked' => true
            ],
            'popularity' => [
                'label' => __('columns.popularity'),
                'checked' => true
            ],
            'title' => [
                'label' => __('columns.title'),
                'checked' => true
            ],
            'badge' => [
                'label' => __('columns.badge'),
                'checked' => false
            ],
            'description' => [
                'label' => __('columns.description'),
                'checked' => false
            ],
            'price' => [
                'label' => __('columns.price'),
                'checked' => true
            ],
            'weight' => [
                'label' => __('columns.weight'),
                'checked' => true
            ],
            'weight_unit' => [
                'label' => __('columns.weight_unit'),
                'checked' => true
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
                'checked' => false
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
