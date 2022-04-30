<?php

namespace App\Subscribers;

use App\Exceptions\MethodNotImplemented;
use App\Traits\StaticMethodsAccess;
use Illuminate\Events\Dispatcher;

/**
 * Class BaseSubscriber.
 */
abstract class BaseSubscriber
{
    use StaticMethodsAccess;

    /**
     * Events map.
     *
     * @var array
     */
    protected array $map = [];

    /**
     * Add listeners to events map.
     *
     * @return void
     */
    abstract protected function map(): void;

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $dispatcher
     *
     * @return void
     * @throws MethodNotImplemented
     */
    public function subscribe(Dispatcher $dispatcher): void
    {
        $this->map();

        foreach ($this->map as $event => $method) {
            $dispatcher->listen($event, static::method($method));
        }
    }
}
