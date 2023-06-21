<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

/**
 * Class RestaurantReview.
 *
 * @mixin \App\Models\RestaurantReview
 */
class RestaurantReview extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\RestaurantReview::class;

    /**
     * Get the value that should be displayed to represent the resource.
     *
     * @return string
     */
    public function title(): string
    {
        if ($this->title) {
            return $this->reviewer . ': ' . $this->title;
        }

        if ($this->description) {
            return $this->reviewer . ': ' . $this->description;
        }

        return $this->reviewer;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'restaurant_id', 'reviewer', 'title', 'description',
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

            Text::make(__('columns.ip'), 'ip')
                ->nullable(),

            Text::make(__('columns.reviewer'), 'reviewer')
                ->rules('required', 'min:1', 'max:255'),

            Number::make(__('columns.review_score'), 'score')
                ->sortable()
                ->required()
                ->min(0)
                ->max(5)
                ->step(1)
                ->rules('required', 'min:0', 'max:5'),

            Text::make(__('columns.review_title'), 'title')
                ->rules('nullable', 'max:255'),

            Text::make(__('columns.review_description'), 'description')
                ->rules('nullable', 'max:510'),

            Boolean::make(__('columns.is_approved'), 'is_approved')
                ->sortable()
                ->default(false),

            Boolean::make(__('columns.is_rejected'), 'is_rejected')
                ->sortable()
                ->default(false),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class),

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
            'ip' => [
                'label' => __('columns.ip'),
                'checked' => true,
            ],
            'reviewer' => [
                'label' => __('columns.reviewer'),
                'checked' => true,
            ],
            'score' => [
                'label' => __('columns.review_score'),
                'checked' => true,
            ],
            'title' => [
                'label' => __('columns.review_title'),
                'checked' => false
            ],
            'description' => [
                'label' => __('columns.review_description'),
                'checked' => false
            ],
            'is_approved' => [
                'label' => __('columns.is_approved'),
                'checked' => true,
            ],
            'is_rejected' => [
                'label' => __('columns.is_rejected'),
                'checked' => true,
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
