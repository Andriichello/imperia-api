<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\User;

class UserController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = User::class;
}
