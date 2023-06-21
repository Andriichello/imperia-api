<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use App\Nova\Options\MorphOptions;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
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
        'id', 'slug', 'target', 'title',
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
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            MediaField::make(__('columns.media'), 'media'),

            Number::make(__('columns.popularity'), 'popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make(__('columns.slug'), 'slug')
                ->rules('required', 'min:1', 'max:50')
                ->creationRules('unique:categories,slug')
                ->updateRules('unique:categories,slug,{{resourceId}}'),

            Select::make(__('columns.target'), 'target')
                ->resolveUsing(fn () => Relation::getMorphedModel($this->target))
                ->options(MorphOptions::categorizable())
                ->nullable()
                ->displayUsingLabels(),

            Text::make(__('columns.title'), 'title')
                ->rules('required', 'min:1', 'max:255'),

            Text::make(__('columns.description'), 'description')
                ->rules('nullable', 'min:1', 'max:255'),


            BelongsToMany::make(__('columns.restaurants'), 'restaurants', Restaurant::class),

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
            'media' => [
                'label' => __('columns.media'),
                'checked' => true,
            ],
            'popularity' => [
                'label' => __('columns.popularity'),
                'checked' => true,
            ],
            'slug' => [
                'label' => 'slug',
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
