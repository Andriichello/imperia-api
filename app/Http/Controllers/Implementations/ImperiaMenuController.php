<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\ImperiaMenuStoreRequest;
use App\Http\Requests\ImperiaMenuUpdateRequest;
use App\Models\ImperiaMenu;

class ImperiaMenuController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = ImperiaMenu::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = ImperiaMenuStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = ImperiaMenuUpdateRequest::class;
}
