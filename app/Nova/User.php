<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;

/**
 * Class User.
 *
 * @mixin \App\Models\User
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'email',
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

            Text::make(__('columns.name'), 'name')
                ->sortable()
                ->rules('required', 'min:2', 'max:255'),

            Text::make(__('columns.email'), 'email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make(__('columns.password'), 'password]')
                ->onlyOnForms()
                ->creationRules('required', 'string', 'min:8')
                ->updateRules('nullable', 'string', 'min:8')
                ->fillUsing(function($request, $model, $attribute, $requestAttribute) {
                    $model->password = ($request[$requestAttribute]);
                }),

            BelongsTo::make(__('columns.restaurant'), 'restaurant', Restaurant::class),

            MorphToMany::make(__('columns.roles'), 'roles', Role::class),

            DateTime::make(__('columns.email_verified_at'), 'email_verified_at')
                ->exceptOnForms(),

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
            'name' => [
                'label' => __('columns.name'),
                'checked' => true,
            ],
            'email' => [
                'label' => __('columns.email'),
                'checked' => true,
            ],
            'email_verified_at' => [
                'label' => __('columns.email_verified_at'),
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
