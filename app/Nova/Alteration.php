<?php

namespace App\Nova;

use App\Models\Morphs\Alteration as AlterationModel;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Nova\Options\WeightUnitOptions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class Alteration.
 *
 * @mixin AlterationModel
 */
class Alteration extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static string $model = AlterationModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Indicates if the resource should be globally searchable.
     *
     * @var bool
     */
    public static $globallySearchable = false;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'metadata', 'alterable_type',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param Request $request
     * @return array
     */
    public function fields(Request $request): array
    {
        $columns = [];

        $target = null;
        $fillMetadataUsing = function (NovaRequest $request, AlterationModel $model, $attribute, $requestAttribute) use (&$target) {
            $old = data_get($target, $attribute);
            $new = $request->get($requestAttribute);

            if ($old !== null) {
                $oldType = gettype($old);
            }

            if ($model->hasInJson('metadata', $attribute)) {
                $old = $model->getFromJson('metadata', $attribute);
            }

            if ($old !== null && empty($oldType)) {
                $oldType = gettype($old);
            }

            if ($new !== null && isset($oldType)) {
                settype($new, $oldType);
            }

            if ($old != $new) {
                $model->setToJson('metadata', $attribute, $new);
            }
        };

        $resolveFromMetadataUsing = function ($value, AlterationModel $resource, $attribute) use (&$target) {
            return $resource->getFromJson('metadata', $attribute, data_get($target, $attribute));
        };

        if ($request instanceof NovaRequest) {
            $viaResource = $request->viaResource ?? $this->alterable_type;
            $viaResourceId = $request->viaResourceId ?? $this->alterable_id;

            if ($viaResource === slugClass(\App\Models\Product::class)) {
                /** @var \App\Models\Product $target */
                $target = \App\Models\Product::query()
                    ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class])
                    ->index($request->user())
                    ->findOrFail($viaResourceId);

                $units = [];

                foreach (WeightUnitOptions::all() as $unit) {
                    $units[$unit] = __('enum.weight_unit.' . $unit);
                }

                $columns = [
                    Boolean::make(__('columns.archived'), 'archived')
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Number::make(__('columns.popularity'), 'popularity')
                        ->step(1)
                        ->sortable()
                        ->nullable()
                        ->displayUsing($resolveFromMetadataUsing)
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Text::make(__('columns.title'), 'title')
                        ->updateRules('sometimes', 'min:1', 'max:255')
                        ->creationRules('required', 'min:1', 'max:255')
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Text::make(__('columns.badge'), 'badge')
                        ->updateRules('nullable', 'min:1', 'max:25')
                        ->creationRules('nullable', 'min:1', 'max:25')
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Textarea::make(__('columns.description'), 'description')
                        ->rules('nullable', 'min:1')
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Number::make(__('columns.price'), 'price')
                        ->step(0.01)
                        ->updateRules('sometimes', 'min:0')
                        ->creationRules('required', 'min:0')
                        ->displayUsing($resolveFromMetadataUsing)
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Text::make(__('columns.weight'), 'weight')
                        ->nullable()
                        ->displayUsing($resolveFromMetadataUsing)
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Select::make(__('columns.weight_unit'), 'weight_unit')
                        ->nullable()
                        ->options($units)
                        ->displayUsing(fn(...$args) => data_get($units, $resolveFromMetadataUsing(...$args) ?? 'non-existing'))
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),
                ];
            }

            if ($viaResource === slugClass(\App\Models\ProductVariant::class)) {
                /** @var \App\Models\ProductVariant $target */
                $target = \App\Models\ProductVariant::query()
                    ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class])
                    ->index($request->user())
                    ->findOrFail($viaResourceId);

                $units = [];

                foreach (WeightUnitOptions::all() as $unit) {
                    $units[$unit] = __('enum.weight_unit.' . $unit);
                }

                $columns = [
                    Number::make(__('columns.price'), 'price')
                        ->step(0.01)
                        ->updateRules('sometimes', 'min:0')
                        ->creationRules('required', 'min:0')
                        ->fillUsing($fillMetadataUsing)
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->displayUsing($resolveFromMetadataUsing),

                    Text::make(__('columns.weight'), 'weight')
                        ->nullable()
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->fillUsing($fillMetadataUsing),

                    Select::make(__('columns.weight_unit'), 'weight_unit')
                        ->nullable()
                        ->options($units)
                        ->resolveUsing($resolveFromMetadataUsing)
                        ->displayUsing(fn(...$args) => data_get($units, $resolveFromMetadataUsing(...$args) ?? 'non-existing'))
                        ->fillUsing($fillMetadataUsing),
                ];
            }
        }


        return [
            ID::make(__('columns.id'), 'id')
                ->sortable(),

            Code::make(__('columns.metadata'), 'metadata')
                ->resolveUsing(fn() => json_encode(json_decode($this->metadata), JSON_PRETTY_PRINT))
                ->autoHeight()
                ->json()
                ->onlyOnDetail()
                ->readonly(),

            MorphTo::make(__('columns.alterable'), 'alterable')
                ->exceptOnForms(),

            DateTime::make(__('columns.perform_at'), 'perform_at')
                ->sortable()
                ->nullable()
                ->rules('sometimes', 'nullable', 'date'),

            DateTime::make(__('columns.performed_at'), 'performed_at')
                ->sortable()
                ->nullable()
                ->rules('sometimes', 'nullable', 'date'),

            ...$columns,

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
            'metadata' => [
                'label' => __('columns.metadata'),
                'checked' => true,
            ],
            'alterable' => [
                'label' => __('columns.alterable'),
                'checked' => true,
            ],
            'perform_at' => [
                'label' => __('columns.perform_at'),
                'checked' => true
            ],
            'performed_at' => [
                'label' => __('columns.performed_at'),
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
