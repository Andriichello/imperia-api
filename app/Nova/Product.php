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
use Laravel\Nova\Fields\Textarea;
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
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        $title = '';

        if ($this->slug && !empty($this->slug)) {
            $title = "$this->slug - ";
        }

        return $title . $this->title;
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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Text::make(__('columns.slug'), 'slug')
                ->rules('required', 'min:1', 'max:50')
                ->creationRules('required', 'unique:products,slug')
                ->updateRules('required', 'unique:products,slug,{{resourceId}}'),

            Boolean::make(__('columns.active'))
                ->resolveUsing(fn() => !$this->archived)
                ->exceptOnForms(),

            Boolean::make(__('columns.archived'), 'archived')
                ->onlyOnForms()
                ->default(fn() => false),

//            MediaField::make(__('columns.media'), 'media'),

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

            Number::make(__('columns.weight'), 'weight')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Select::make(__('columns.weight_unit'), 'weight_unit')
                ->options(WeightUnitOptions::all())
                ->default(WeightUnit::Gram),

            HasMany::make(__('columns.variants'), 'variants', ProductVariant::class),

            BelongsToMany::make(__('columns.menus'), 'menus', Menu::class),

            MorphToMany::make(__('columns.categories'), 'categories', Category::class),

            BelongsToMany::make(__('columns.restaurants'), 'restaurants', Restaurant::class),

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
            'active' => [
                'label' => __('columns.active'),
                'checked' => true
            ],
//            'media' => [
//                'label' => __('columns.media'),
//                'checked' => true
//            ],
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
