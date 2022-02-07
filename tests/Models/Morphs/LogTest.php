<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Log;
use Tests\Models\Stubs\LoggableStub;
use Tests\StubsTestCase;

/**
 * Class LogTest.
 */
class LogTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var LoggableStub
     */
    protected LoggableStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new LoggableStub();
        $this->instance->saveQuietly();
    }

    /**
     * Test loggable relation.
     *
     * @return void
     */
    public function testLoggable()
    {
        $log = Log::factory()->withMetadata(['title' => 'Just a title'])
            ->withModel($this->instance)->create();

        $this->assertEquals(1, $this->instance->logs()->count());

        $this->assertNotEmpty($log->loggable);
        $this->assertEquals($this->instance->id, $log->loggable->id);
        $this->assertEquals($this->instance->type, $log->loggable->type);
        $this->assertTrue($log->loggable instanceof $this->instance);
    }
}
