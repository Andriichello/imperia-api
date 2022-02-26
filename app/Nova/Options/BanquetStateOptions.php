<?php

namespace App\Nova\Options;

use App\Enums\BanquetState;
use App\Helpers\BanquetHelper;
use App\Models\Banquet;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class BanquetStateOptions.
 */
class BanquetStateOptions extends Options
{
    /**
     * Get all options.
     *
     * @return array
     */
    public static function all(): array
    {
        $options = [];
        foreach (BanquetState::getValues() as $state) {
            $options[$state] = $state;
        }
        return $options;
    }

    /**
     * Get all states, to which banquet can be transferred.
     *
     * @param Request|NovaRequest $request
     * @param Banquet $banquet
     *
     * @return array
     */
    public static function available(Request|NovaRequest $request, Banquet $banquet): array
    {
        $isCreating = $request instanceof NovaRequest && $request->isCreateOrAttachRequest();
        $states = (new BanquetHelper())->availableTransferStates($isCreating ? null : $banquet);

        $options = [];
        foreach ($states as $state) {
            $options[$state] = $state;
        }
        return $options;
    }
}
