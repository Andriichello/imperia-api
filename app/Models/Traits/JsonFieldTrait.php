<?php

namespace App\Models\Traits;

use App\Models\BaseModel;

/**
 * Trait JsonFieldTrait.
 *
 * @mixin BaseModel
 */
trait JsonFieldTrait
{
    /**
     * Get JSON field decoded as associate array.
     *
     * @param string $field
     *
     * @return array
     */
    public function getJson(string $field): array
    {
        return !is_string($this->$field) ? $this->$field :
            (array)json_decode($this->$field, true);
    }

    /**
     * Get JSON field's property value by key.
     *
     * @param string $field
     * @param string|int $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getFromJson(string $field, string|int $key, mixed $default = null): mixed
    {
        $array = $this->getJson($field);
        return $key ? data_get($array, $key, $default) : $array;
    }

    /**
     * Set JSON field.
     *
     * @param string $field
     * @param array $array
     *
     * @return void
     */
    public function setJson(string $field, array $array): void
    {
        $this->$field = $array;
    }

    /**
     * Set JSON field's property value by key.
     *
     * @param string $field
     * @param string|int $key
     * @param mixed $value
     *
     * @return void
     */
    public function setToJson(string $field, string|int $key, mixed $value): void
    {
        $array = $this->getJson($field);
        data_set($array, $key, $value);
        $this->$field = $array;
    }
}
