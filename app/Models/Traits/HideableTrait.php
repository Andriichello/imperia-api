<?php

namespace App\Models\Traits;

/**
 * Trait HideableTrait.
 *
 * Hidden models (`is_hidden`) are left out for guests by `ArchivedScope`, together with
 * archived ones, so every place that shows archived models to staff shows hidden ones too.
 *
 * @property bool $is_hidden
 */
trait HideableTrait
{
    /**
     * Whether the model is hidden from guests.
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return (bool) $this->getAttribute('is_hidden');
    }
}
