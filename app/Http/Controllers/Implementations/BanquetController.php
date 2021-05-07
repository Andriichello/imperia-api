<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\Banquet;

class BanquetController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Banquet::class;
}
