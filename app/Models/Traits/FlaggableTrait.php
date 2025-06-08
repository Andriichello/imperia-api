<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use Illuminate\Support\Arr;

/**
 * Trait FlaggableTrait.
 *
 * @mixin BaseModel
 *
 * @property string[] $flags
 */
trait FlaggableTrait
{
    /**
     * Accessor for the `flags` attribute.
     *
     * @return string[]
     */
    public function getFlagsAttribute(): array
    {
        $flags = $this->getFromJson('metadata', 'flags');

        if (empty($flags)) {
            return [];
        }

        return Arr::wrap($flags);
    }

    /**
     * Mutator for the `flags` attribute.
     *
     * @param string[] $flags
     *
     * @return void
     */
    public function setFlagsAttribute(array $flags): void
    {
        $this->setToJson('metadata', 'flags', $flags);
    }

    /**
     * Attach given flags to the model.
     *
     * @param string ...$flags
     *
     * @return static
     */
    public function attachFlags(string ...$flags): static
    {
        $array = $this->flags;

        foreach ($flags as $flag) {
            if (!in_array($flag, $array)) {
                $array[] = $flag;
                $added = true;
            }
        }

        if (isset($added)) {
            $this->flags = $array;
        }

        return $this;
    }

    /**
     * Detach given flags from the model.
     *
     * @param string ...$flags
     *
     * @return static
     */
    public function detachFlags(string ...$flags): static
    {
        $array = $this->flags;

        foreach ($flags as $index => $flag) {
            if (in_array($flag, $array)) {
                unset($array[$index]);
                $removed = true;
            }
        }

        if (isset($removed)) {
            $this->flags = $array;
        }

        return $this;
    }

    /**
     * Determines if model has flags attached.
     *
     * @return bool
     */
    public function hasFlags(): bool
    {
        return !empty($this->flags);
    }

    /**
     * Determines if model has all flags attached.
     *
     * @param string ...$flags
     *
     * @return bool
     */
    public function hasAllOfFlags(string ...$flags): bool
    {
        $array = $this->flags;

        foreach ($flags as $flag) {
            if (!in_array($flag, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determines if model has any of flags attached.
     *
     * @param string ...$flags
     *
     * @return bool
     */
    public function hasAnyOfFlags(string ...$flags): bool
    {
        $array = $this->flags;

        foreach ($flags as $flag) {
            if (in_array($flag, $array)) {
                return true;
            }
        }

        return false;
    }
}
