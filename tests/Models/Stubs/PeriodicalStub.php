<?php

namespace Tests\Models\Stubs;

use App\Models\Interfaces\PeriodicalInterface;
use App\Models\Traits\PeriodicalTrait;

/**
 * Class PeriodicalStub.
 */
class PeriodicalStub extends BaseStub implements PeriodicalInterface
{
    use PeriodicalTrait;
}
