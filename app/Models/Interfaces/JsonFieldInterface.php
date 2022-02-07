<?php

namespace App\Models\Interfaces;

/**
 * Interface JsonFieldInterface.
 */
interface JsonFieldInterface
{
    /**
     * Get JSON field decoded as associate array.
     *
     * @param string $field
     *
     * @return array
     */
    public function getJson(string $field): array;

    /**
     * Get JSON field's property value by key.
     *
     * @param string $field
     * @param string|int $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getFromJson(string $field, string|int $key, mixed $default = null): mixed;

    /**
     * Set JSON field.
     *
     * @param string $field
     * @param array $array
     *
     * @return void
     */
    public function setJson(string $field, array $array): void;

    /**
     * Set JSON field's property value by key.
     *
     * @param string $field
     * @param string|int $key
     * @param mixed $value
     *
     * @return void
     */
    public function setToJson(string $field, string|int $key, mixed $value): void;
}
