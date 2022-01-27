<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
