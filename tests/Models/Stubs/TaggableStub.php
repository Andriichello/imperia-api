<?php

namespace Tests\Models\Stubs;

use App\Models\Interfaces\TaggableInterface;
use App\Models\Traits\TaggableTrait;

/**
 * Class TaggableStub.
 */
class TaggableStub extends BaseStub implements TaggableInterface
{
    use TaggableTrait;
}
