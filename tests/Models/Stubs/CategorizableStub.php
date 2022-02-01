<?php

namespace Tests\Models\Stubs;

use App\Models\Interfaces\CategorizableInterface;
use App\Models\Traits\CategorizableTrait;

/**
 * Class CategorizableStub.
 */
class CategorizableStub extends BaseStub implements CategorizableInterface
{
    use CategorizableTrait;
}
