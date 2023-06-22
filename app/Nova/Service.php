<?php

namespace App\Nova;

use Andriichello\Media\MediaField;
use App\Models\Scopes\ArchivedScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Service.
 *
 * @mixin \App\Models\Service
 */
class Service extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\Service::class;

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
                ->creationRules('unique:services,slug')
                ->updateRules('unique:services,slug,{{resourceId}}'),

            Boolean::make('Active')
                ->resolveUsing(fn() => !$this->archived)
                ->exceptOnForms(),

            Boolean::make('Archived')
                ->onlyOnForms()
                ->default(fn() => false),

//            MediaField::make(__('columns.media'), 'media'),

            Number::make('Popularity')
                ->step(1)
                ->sortable()
                ->nullable(),

            Text::make(__('columns.title'), 'title')
                ->updateRules('sometimes', 'min:1', 'max:255')
                ->creationRules('required', 'min:1', 'max:255'),

            Textarea::make(__('columns.description'), 'description')
                ->rules('nullable', 'min:1'),

            Number::make('Once Paid Price')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            Number::make('Hourly Paid Price')
                ->step(0.01)
                ->updateRules('sometimes', 'min:0')
                ->creationRules('required', 'min:0'),

            MorphToMany::make('Categories'),

            BelongsToMany::make('Restaurants'),

            MorphMany::make('Logs', 'logs', Log::class),

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
                'checked' => true,
            ],
            'slug' => [
                'label' => __('columns.slug'),
                'checked' => true,
            ],
            'active' => [
                'label' => __('columns.active'),
                'checked' => true,
            ],
//            'media' => [
//                'label' => __('columns.media'),
//                'checked' => true,
//            ],
            'popularity' => [
                'label' => __('columns.popularity'),
                'checked' => true,
            ],
            'title' => [
                'label' => __('columns.title'),
                'checked' => false,
            ],
            'description' => [
                'label' => __('columns.description'),
                'checked' => false,
            ],
            'once_paid_price' => [
                'label' => __('columns.once_paid_price'),
                'checked' => true,
            ],
            'hourly_paid_price' => [
                'label' => __('columns.hourly_paid_price'),
                'checked' => true,
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
