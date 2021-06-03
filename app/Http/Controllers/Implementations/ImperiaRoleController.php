<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\ImperiaRoleStoreRequest;
use App\Http\Requests\ImperiaRoleUpdateRequest;
use App\Models\ImperiaRole;

class ImperiaRoleController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = ImperiaRole::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = ImperiaRoleStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = ImperiaRoleUpdateRequest::class;
}
