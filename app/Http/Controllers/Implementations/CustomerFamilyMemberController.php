<?php

namespace App\Http\Controllers\Implementations;

use App\Http\Controllers\DynamicController;
use App\Http\Requests\CustomerFamilyMemberStoreRequest;
use App\Http\Requests\CustomerFamilyMemberUpdateRequest;
use App\Models\CustomerFamilyMember;

class CustomerFamilyMemberController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = CustomerFamilyMember::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = CustomerFamilyMemberStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = CustomerFamilyMemberUpdateRequest::class;
}
