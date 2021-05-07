<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\BanquetState;

class BanquetStateController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = BanquetState::class;
}
