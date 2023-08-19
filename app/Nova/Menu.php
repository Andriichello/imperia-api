<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use App\Models\Scopes\ArchivedScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Menu.
 *
 * @mixin \App\Models\Menu
 */
class Menu extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Menu::class;

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
            Rule::unique('menus', 'slug')
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
                ->resolveUsing(fn() => !$this->archived)
                ->exceptOnForms(),

            Boolean::make(__('columns.archived'), 'archived')
                ->onlyOnForms()
                ->default(fn() => false),

            MediaField::make(__('columns.media'), 'media')
                ->canSee(fn() => !$request->user()->isPreviewOnly()),

            Number::make(__('columns.popularity'), 'popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make(__('columns.title'), 'title')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Textarea::make(__('columns.description'), 'description')
                ->rules('nullable', 'min:1'),

            HasMany::make(__('columns.categories'), 'categories', Category::class)
                ->readonly(),

            HasMany::make(__('columns.products'), 'products', Product::class),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class)
                ->default(fn() => $user->restaurant_id),

            DateTime::make(__('columns.created_at'), 'created_at')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make(__('columns.updated_at'))
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
            'title' => [
                'label' => __('columns.title'),
                'checked' => true,
            ],
            'description' => [
                'label' => __('columns.description'),
                'checked' => false
            ],
            'restaurant' => [
                'label' => __('columns.restaurant'),
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
