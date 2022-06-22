<?php

namespace Andriichello\ColumnsCard;

use JsonSerializable;

/**
 * Class Field.
 */
class Field implements JsonSerializable
{
    public string $label;
    public string $attribute;

    public bool $checked;

    /**
     * @param bool $checked
     * @param string $attribute
     * @param string|null $label
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $attribute, bool $checked = false, string $label = null)
    {
        $this->checked = $checked;
        $this->attribute = $attribute;
        $this->label = $label ?? $attribute;
    }

    /**
     * Prepare the element for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'label' => $this->label,
            'checked' => $this->checked,
            'attribute' => $this->attribute,
        ];
    }
}
