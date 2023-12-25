<?php

namespace App\Repositories;

use App\Models\Morphs\Tip;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TipRepository.
 */
class TipRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Tip::class;
}
