<?php

namespace App\Http\Resources\FamilyMember;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class FamilyMemberCollection.
 */
class FamilyMemberCollection extends ResourceCollection
{
    public $collects = FamilyMemberResource::class;
}
