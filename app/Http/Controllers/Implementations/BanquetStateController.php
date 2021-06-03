<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\BanquetStateStoreRequest;
use App\Http\Requests\BanquetStateUpdateRequest;
use App\Models\BanquetState;

class BanquetStateController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = BanquetState::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = BanquetStateStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = BanquetStateUpdateRequest::class;
}
