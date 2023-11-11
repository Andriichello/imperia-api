<?php

namespace App\Helpers;

use App\Enums\BanquetState;
use App\Helpers\Interfaces\BanquetHelperInterface;
use App\Models\Banquet;

/**
 * Class BanquetHelper.
 */
class BanquetHelper implements BanquetHelperInterface
{
    /**
     * Determine if banquet can be transferred to state.
     *
     * @param Banquet $banquet
     * @param BanquetState|string $state
     *
     * @return bool
     */
    public function canTransfer(Banquet $banquet, BanquetState|string $state): bool
    {
        $state = $state instanceof BanquetState ? $state->value : $state;
        return in_array($state, $this->availableTransferStates($banquet));
    }

    /**
     * Get array of available banquet transfer states.
     *
     * @param Banquet|null $banquet
     *
     * @return array
     */
    public function availableTransferStates(?Banquet $banquet): array
    {
        return BanquetState::getValues();
    }
}
