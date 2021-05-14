<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\ImperiaRole;

class RoleController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = ImperiaRole::class;
}
