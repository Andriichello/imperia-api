<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Alteration;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface AlterableInterface.
 *
 * @property Alteration[]|Collection $alterations
 */
interface AlterableInterface
{
    /**
     * Alterations related to the model.
     *
     * @return MorphMany
     */
    public function alterations(): MorphMany;

    /**
     * Attach given alterations to the model.
     *
     * @param Alteration ...$alterations
     *
     * @return void
     */
    public function attachAlterations(Alteration ...$alterations): void;

    /**
     * Detach given alterations from the model.
     *
     * @param Alteration ...$alterations
     *
     * @return void
     */
    public function detachAlterations(Alteration ...$alterations): void;

    /**
     * Determines if model has alterations attached.
     *
     * @return bool
     */
    public function hasAlterations(): bool;

    /**
     * Determines if model has pending alterations (which
     * should have already been performed) attached.
     *
     * @return bool
     */
    public function hasPendingAlterations(): bool;
}
