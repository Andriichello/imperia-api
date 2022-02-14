<?php

namespace App\Helpers\Interfaces;

use App\Enums\BanquetState;
use App\Models\Banquet;

/**
 * Interface BanquetHelperInterface.
 */
interface BanquetHelperInterface
{
    /**
     * Determine if banquet can be transferred to state.
     *
     * @param Banquet $banquet
     * @param BanquetState|string $state
     *
     * @return bool
     */
    public function canTransfer(Banquet $banquet, BanquetState|string $state): bool;

    /**
     * Get array of available banquet transfer states.
     *
     * @param Banquet $banquet
     *
     * @return array
     */
    public function availableTransferStates(Banquet $banquet): array;
}
