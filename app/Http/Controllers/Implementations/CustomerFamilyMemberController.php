<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Models\CustomerFamilyMember;

class CustomerFamilyMemberController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected $model = CustomerFamilyMember::class;
}
