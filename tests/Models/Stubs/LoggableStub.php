<?php

namespace Tests\Models\Stubs;

use App\Models\Interfaces\LoggableInterface;
use App\Models\Traits\LoggableTrait;

/**
 * Class LoggableStub.
 */
class LoggableStub extends BaseStub implements LoggableInterface
{
    use LoggableTrait;

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'name',
    ];
}
