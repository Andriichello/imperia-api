<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class StubsTestCase.
 */
abstract class StubsTestCase extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createStubsTable();
    }

    /**
     * Create stubs table.
     *
     * @return void
     */
    protected function createStubsTable(): void
    {
        Schema::create('stubs', function (Blueprint $table) {
            $table->temporary();
            $table->increments('id');
            $table->string('name')->default('Stub');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
