<?php

namespace Andriichello\ColumnsCard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\MergeValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Field as NovaField;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\FilterDecoder;
use Laravel\Nova\Http\Controllers\ActionController;
use Laravel\Nova\Http\Controllers\FilterController;
use Laravel\Nova\Http\Controllers\LensController;
use Laravel\Nova\Http\Controllers\ResourceCountController;
use Laravel\Nova\Http\Controllers\ResourceIndexController;
use Laravel\Nova\Http\Controllers\ResourceShowController;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Trait HasColumnsFilter.
 */
trait HasColumnsFilter
{
    /**
     * Get field classes that shouldn't be displayed on index
     *
     * @return array
     */
    protected function unavailableFieldClasses(): array
    {
        return [
            HasMany::class,
            BelongsToMany::class,
        ];
    }

    /**
     * Determine if field should be available based on class.
     *
     * @param object $field
     *
     * @return bool
     */
    public function shouldBeAvailable(object $field): bool
    {
        return $field instanceof NovaField &&
            !in_array(get_class($field), $this->unavailableFieldClasses());
    }

    /**
     * Get current resource name.
     *
     * @return string
     */
    public function getResourceName(): string
    {
        return Str::of(static::class)
            ->explode('\\')
            ->last();
    }

    /**
     * Get current resource slug.
     *
     * @return string
     */
    public function getResourceSlug(): string
    {
        return Str::snake($this->getResourceName(), '-');
    }

    /**
     * Get current resource's filter storage key.
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        return $this->getResourceSlug() . '-columns-filter-fields';
    }

    /**
     * Get currently checked attributes.
     *
     * @return array
     */
    public function getFilteredAttributes(): array
    {
        $decoder = new FilterDecoder(request('filters'));
        $filter = collect($decoder->decodeFromBase64String())
            ->first(fn($f) => data_get($f, ColumnsFilter::class));

        $attributes = data_get($filter, ColumnsFilter::class);
        if (!is_array($attributes)) {
            return session($this->getCacheKey(), []);
        }

        return $attributes;
    }

    /**
     * Get current resource's mega filter card instance.
     *
     * @param NovaRequest $request
     *
     * @return ColumnsCard|null
     */
    private function getColumnsCard(NovaRequest $request): ?ColumnsCard
    {
        $cacheKey = $this->getCacheKey();

        $columnsCards = collect($this->cards($request))
            ->whereInstanceOf(ColumnsCard::class)
            ->filter(function (ColumnsCard $card) use ($cacheKey) {
                return data_get($card, 'settings.cache.key') === $cacheKey;
            });

        return $columnsCards->first();
    }

    /**
     * Get collection of columns to be displayed.
     *
     * @param FieldCollection $available
     * @return FieldCollection
     */
    private function getCheckedFields(FieldCollection $available): FieldCollection
    {
        $attributes = $this->getFilteredAttributes();
        return $available->filter(fn($field) => in_array($field->attribute, $attributes));
    }

    /**
     * Check if current mega filter should be applied.
     *
     * @param NovaRequest $request
     * @return bool
     */
    private function shouldApplyColumnsFilter(NovaRequest $request): bool
    {
        $controller = data_get($request->route(), 'controller');

        if ($controller instanceof ActionController && $request->method() === 'POST') {
            return true;
        }

        if (
            $request->viaRelationship() ||
            $controller instanceof ResourceShowController
        ) {
            return false;
        }

        return $controller instanceof FilterController
            || $controller instanceof ResourceIndexController
            || $controller instanceof ResourceCountController
            || $controller instanceof LensController;
    }

    /**
     * Get the filters for the given request.
     *
     * @param NovaRequest $request
     *
     * @return Collection
     */
    public function resolveFilters(NovaRequest $request): Collection
    {
        if ($this->shouldApplyColumnsFilter($request)) {
            $attributes = $this->getFilteredAttributes();

            if ($attributes) {
                session([$this->getCacheKey() => $attributes]);
            }
        }

        return parent::resolveFilters($request);
    }

    /**
     * Get the fields that are available for the given request.
     *
     * @param NovaRequest $request
     * @return FieldCollection
     */
    public function availableFields(NovaRequest $request): FieldCollection
    {
        $available = parent::availableFields($request);
        if (!$this->shouldApplyColumnsFilter($request)) {
            return $available;
        }

        $mapper = fn($field) => $field instanceof MergeValue ? $field->data : $field;
        $filter = fn($field) => $this->shouldBeAvailable($field);

        $available = $available
            ->map($mapper)
            ->flatten()
            ->filter($filter);

        return $this->getCheckedFields($available);
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
            'attribute' => [
                'label' => 'Custom Label',
                'checked' => false,
            ],
        ];
    }

    /**
     * Make new instance columns filter Field.
     *
     * @param string $attribute
     * @param array|bool $options
     * @return Field
     */
    protected function makeColumnsFilterField(string $attribute, array|bool $options = []): Field
    {
        $label = data_get($options, 'label', labelize($attribute));
        $checked = data_get($options, 'checked', $options === true);

        return new Field($attribute, $checked, $label);
    }

    /**
     * Get ColumnsCard settings.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function columnsCardSettings(Request $request): array
    {
        return [
            'title' => 'Columns',

            'cache' => ['key' => $this->getCacheKey()],

            'resource' => [
                'name' => $this->getResourceName(),
                'class' => static::class,
            ],

            'filter' => [
                'class' => ColumnsFilter::class,
            ]
        ];
    }

    /**
     * Get new ColumnsCard instance.
     *
     * @param Request $request
     *
     * @return ColumnsCard
     */
    protected function makeColumnsCard(Request $request): ColumnsCard
    {
        $settings = $this->columnsCardSettings($request);

        $fields = [];
        foreach ($this->columnsFilterFields($request) as $attribute => $options) {
            $fields[] = $this->makeColumnsFilterField($attribute, $options);
        }

        $card = new ColumnsCard();
        $card->setSettings($settings);
        $card->setFields($fields);
        return $card;
    }

    /**
     * Get new ColumnsFilter instance.
     *
     * @param Request $request
     *
     * @return ColumnsFilter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function makeColumnsFilter(Request $request): ColumnsFilter
    {
        return new ColumnsFilter();
    }
}
