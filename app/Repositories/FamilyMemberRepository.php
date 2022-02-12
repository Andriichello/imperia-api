<?php

namespace App\Repositories;

use App\Models\FamilyMember;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FamilyMemberRepository.
 */
class FamilyMemberRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = FamilyMember::class;
}
