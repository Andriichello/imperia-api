<?php

namespace App\Models\Interfaces;

/**
 * Interface HideableInterface.
 *
 * Models that can be hidden from guests for a while (`is_hidden`), while staying in
 * the admin lists. Unlike archived ones, they aren't moved out of those lists.
 * Guests get neither, see `ArchivedScope`.
 */
interface HideableInterface
{
    /**
     * Whether the model is hidden from guests.
     *
     * @return bool
     */
    public function isHidden(): bool;
}
