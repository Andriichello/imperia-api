<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Class TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * If true, database should be seeded after running migrations.
     * @var bool
     */
    protected bool $seed = true;
}
