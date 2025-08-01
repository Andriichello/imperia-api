<?php

namespace Tests\Models\Stubs;

use App\Models\Interfaces\DiscountableInterface;
use App\Models\Traits\DiscountableTrait;

/**
 * Class DiscountableStub.
 */
class DiscountableStub extends BaseStub implements DiscountableInterface
{
    use DiscountableTrait;
}
