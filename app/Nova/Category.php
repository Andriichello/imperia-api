<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use App\Models\Scopes\ArchivedScope;
use App\Nova\Options\MorphOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Category.
 *
 * @mixin \App\Models\Morphs\Category
 */
class Category extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Morphs\Category::class;

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
        'id', 'slug', 'target', 'title',
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
        $query = parent::indexQuery($request, $query);

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
            Rule::unique('categories', 'slug')
                ->where(function ($query) use ($user, $resourceId) {
                    if ($resourceId) {
                        $query->where('id', '!=', $resourceId);
                    }

                    if ($user->restaurant_id) {
                        $query->where('restaurant_id', $user->restaurant_id);
                    }
                }),
        ];

        return [
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Text::make(__('columns.slug'), 'slug')
                ->rules($slugValidationRules),

            Boolean::make(__('columns.active'))
                ->exceptOnForms()
                ->resolveUsing(fn() => !$this->archived),

            Boolean::make(__('columns.archived'), 'archived')
                ->onlyOnForms()
                ->default(fn() => true),

            MediaField::make(__('columns.media'), 'media')
                ->canSee(fn() => !$user->isPreviewOnly()),

            Number::make(__('columns.popularity'), 'popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Select::make(__('columns.target'), 'target')
                ->resolveUsing(fn () => Relation::getMorphedModel($this->target))
                ->options(MorphOptions::categorizable())
                ->default(\App\Models\Product::class)
                ->nullable()
                ->displayUsingLabels(),

            Text::make(__('columns.title'), 'title')
                ->rules('required', 'min:1', 'max:255'),

            Text::make(__('columns.description'), 'description')
                ->rules('nullable', 'min:1', 'max:255'),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class)
                ->default(fn() => $user->restaurant_id),

            DateTime::make(__('columns.created_at'), 'created_at')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make(__('columns.updated_at'), 'updated_at')
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
            ->whereIn('target', [$target, null]);
    }

    /**
     * Determine if the current user can create new resources.
     *
     * @param Request $request
     *
     * @return bool
     */
    public static function authorizedToCreate(Request $request): bool
    {
        if ($request->get('viaResource')) {
            return false;
        }

        return parent::authorizedToCreate($request);
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
            'slug' => [
                'label' => __('columns.slug'),
                'checked' => true,
            ],
            'media' => [
                'label' => __('columns.media'),
                'checked' => true,
            ],
            'popularity' => [
                'label' => __('columns.popularity'),
                'checked' => true,
            ],
            'target' => [
                'label' => __('columns.target'),
                'checked' => true,
            ],
            'title' => [
                'label' => __('columns.title'),
                'checked' => true,
            ],
            'description' => [
                'label' => __('columns.description'),
                'checked' => false,
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
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
