<?php

namespace App\Repositories;

use App\Models\Morphs\Media;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MediaRepository.
 */
class MediaRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Media::class;
}
