<?php

namespace Andriichello\ColumnsCard;

use Illuminate\Support\Collection;
use Laravel\Nova\Card;

/**
 * Class ColumnsCard.
 */
class ColumnsCard extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Array of fields and their options.
     *
     * @var Field[]
     */
    public array $fields = [];

    /**
     * Array of columns card settings
     * @var array
     */
    public array $settings = [
        'title' => 'Columns',

        'cache' => [
            'key' => 'columns-card-fields'
        ],

        'resource' => [
            'name' => 'Resource',
            'class' => 'Resource',
        ],

        'filter' => [
            'class' => 'Filter',
        ],
    ];

    /**
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        parent::__construct();
        $this->setSettings($settings);
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component(): string
    {
        return 'columns-card';
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return collect($this->fields);
    }

    /**
     * @param array $fields
     *
     * @return static
     */
    public function setFields(array $fields): static
    {
        $this->fields = [];

        foreach ($fields as $attribute => $value) {
            if ($value instanceof Field) {
                $this->fields[] = $value;
                continue;
            }

            $label = data_get($value, 'label');
            $checked = data_get($value, 'checked', false);

            $this->fields[] = new Field($attribute, $checked, $label);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     * @param string|null $key
     *
     * @return static
     */
    public function setSettings(mixed $settings, string $key = null): static
    {
        if (isset($key)) {
            data_set($this->settings, $key, $settings);
        }
        if (is_array($settings)) {
            $this->settings = $settings;
        }
        return $this;
    }

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'fields' => $this->fields,
            'settings' => $this->settings,
        ], parent::jsonSerialize());
    }
}
