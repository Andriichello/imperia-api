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
        $json = $this->$field ?? [];

        return is_string($json)
            ? (array)json_decode($json, true)
            : $json;
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
        $this->$field = json_encode($array);
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
        $this->$field = json_encode($array);
    }
}
