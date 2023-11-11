<?php

namespace App\Imports;

use App\Imports\Interfaces\TransformerInterface;

/**
 * Class AbstractTransformer.
 */
abstract class AbstractTransformer implements TransformerInterface
{
    /**
     * If true then strings will be trimmed of
     * trailing spaces.
     *
     * @var bool
     */
    protected bool $trimSpaces = true;

    /**
     * If true then empty strings are
     * being cast to null in prepare method.
     *
     * @var bool
     */
    protected bool $emptyAsNull = true;

    /**
     * Transform record to be imported.
     *
     * @param array $record
     *
     * @return array
     */
    public function transform(array $record): array
    {
        return $this->prepare($record);
    }

    /**
     * Prepare record before transforming.
     *
     * @param array $record
     *
     * @return array
     */
    public function prepare(array $record): array
    {
        $prepared = [];

        foreach ($record as $key => $value) {
            if (is_string($value)) {
                if ($this->trimSpaces) {
                    $value = trim($value);
                }

                if ($this->emptyAsNull && empty($value)) {
                    $value = null;
                }
            }

            $prepared[$key] = empty($value) ? null : $value;
        }

        return $prepared;
    }
}
