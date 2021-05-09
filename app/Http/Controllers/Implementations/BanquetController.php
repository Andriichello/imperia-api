<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Constrainter;
use App\Http\Controllers\DynamicController;
use App\Models\Banquet;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

class BanquetController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = Banquet::class;
}
