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
        if (empty($banquet)) {
            return [BanquetState::Draft];
        }

        $states = match ($banquet->state) {
            BanquetState::Draft => [BanquetState::New],
            BanquetState::New => [BanquetState::Processing, BanquetState::Cancelled],
            BanquetState::Processing => [BanquetState::New, BanquetState::Cancelled, BanquetState::Completed],
            BanquetState::Cancelled => [BanquetState::New, BanquetState::Processing],
            default => [],
        };

        array_unshift($states, $banquet->state);
        return $states;
    }
}
