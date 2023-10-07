<?php

namespace App\Repositories;

use App\Models\Morphs\Tag;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TagRepository.
 */
class TagRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Tag::class;
}
