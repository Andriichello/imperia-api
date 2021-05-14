<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\ImperiaMenu;

class MenuController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = ImperiaMenu::class;
}
