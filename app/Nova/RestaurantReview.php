<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
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
            ID::make()->sortable(),

            Text::make('Ip')
                ->rules(
                    'nullable',
                    Rule::unique('restaurant_reviews', 'ip')
                        ->where('restaurant_id', $this->restaurant_id),
                ),

            Text::make('Reviewer')
                ->rules('required', 'min:1', 'max:255'),

            Number::make('Score')
                ->sortable()
                ->min(0)
                ->max(5)
                ->step(1)
                ->rules('nullable', 'min:0', 'max:5'),

            Text::make('Title')
                ->rules('nullable', 'max:255'),

            Text::make('Description')
                ->rules('nullable', 'max:510'),

            BelongsTo::make('Restaurant'),

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
            'ip' => true,
            'reviewer' => true,
            'score' => true,
            'title' => true,
            'description' => false,
            'created_at' => false,
            'updated_at' => false,
        ];
    }
}
