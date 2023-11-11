<?php

namespace App\Nova;

use App\Nova\Options\MorphOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Tag.
 *
 * @mixin \App\Models\Morphs\Tag
 */
class Tag extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Morphs\Tag::class;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'target', 'title',
    ];

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

        $titleValidationRules = [
            'required',
            'min:1',
            'max:255',
            Rule::unique('tags', 'title')
                ->where(function ($query) use ($user, $resourceId) {
                    if ($resourceId) {
                        $query->where('id', '!=', $resourceId);
                    }

                    if ($user->restaurant_id) {
                        $query->where('restaurant_id', $user->restaurant_id);
                    }
                }),
        ];

        $taggables = MorphOptions::taggable();

        return [
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Select::make(__('columns.target'), 'target')
                ->resolveUsing(fn () => Relation::getMorphedModel($this->target))
                ->options($taggables)
                ->default(\App\Models\Product::class)
                ->nullable()
                ->displayUsing(fn($val) => data_get($taggables, Relation::getMorphedModel($val))),

            Text::make(__('columns.title'), 'title')
                ->rules($titleValidationRules),

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
     * @param Builder $query
     *
     * @return Builder
     */
    public static function relatableQuery(NovaRequest $request, $query): Builder
    {
        $target = slugClass($request->resource());
        $targets = [$target, null];

        if ($target === slugClass(Category::class)) {
            /** @var \App\Models\Morphs\Category $category */
            $category = \App\Models\Morphs\Category::query()
                ->find($request->resourceId);
            // @phpstan-ignore-next-line
            if ($category) {
                $targets[] = $category->target;
            }
        }

        // @phpstan-ignore-next-line
        return parent::relatableQuery($request, $query)
            ->whereIn('target', $targets);
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
