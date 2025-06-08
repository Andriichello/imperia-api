<?php

namespace App\Models\Interfaces;

/**
 * Interface FlaggableInterface.
 *
 * @property string[] $flags
 */
interface FlaggableInterface
{
    /**
     * Accessor for the `flags` attribute.
     *
     * @return string[]
     */
    public function getFlagsAttribute(): array;

    /**
     * Mutator for the `flags` attribute.
     *
     * @param string[] $flags
     *
     * @return void
     */
    public function setFlagsAttribute(array $flags): void;

    /**
     * Attach given flags to the model.
     *
     * @param string ...$flags
     *
     * @return static
     */
    public function attachFlags(string ...$flags): static;

    /**
     * Detach given flags from the model.
     *
     * @param string ...$flags
     *
     * @return static
     */
    public function detachFlags(string ...$flags): static;

    /**
     * Determines if model has flags attached.
     *
     * @return bool
     */
    public function hasFlags(): bool;

    /**
     * Determines if model has all flags attached.
     *
     * @param string ...$flags
     *
     * @return bool
     */
    public function hasAllOfFlags(string ...$flags): bool;

    /**
     * Determines if model has any of flags attached.
     *
     * @param string ...$flags
     *
     * @return bool
     */
    public function hasAnyOfFlags(string ...$flags): bool;
}
